<?php

namespace App\Services;

use App\Models\{PengaturanKapasitas, PenyesuaianKapasitasHarian, Pesanan};
use Carbon\{Carbon, CarbonPeriod};

class CuttingCapacityService
{
    public function hitungKapasitasTersedia(string $tanggalDeadline): array
    {
        $setting = PengaturanKapasitas::first();
        $kapasitasStandar = ($setting->jumlah_operator_cutting ?? 2) * ($setting->jam_kerja_per_hari ?? 8);

        $mulai = Carbon::today();
        $akhir = Carbon::parse($tanggalDeadline);

        if ($akhir->lt($mulai)) {
            return ['total_kapasitas' => 0, 'beban_terpakai' => 0, 'sisa_kapasitas' => 0];
        }

        $periode = CarbonPeriod::create($mulai, $akhir);
        $penyesuaian = PenyesuaianKapasitasHarian::whereBetween('tanggal', [$mulai->format('Y-m-d'), $akhir->format('Y-m-d')])
            ->pluck('kapasitas_jam_khusus', 'tanggal')
            ->toArray();

        $totalKapasitas = 0;
        foreach ($periode as $tgl) {
            if ($tgl->isSunday()) continue;
            $tglStr = $tgl->format('Y-m-d');
            $totalKapasitas += $penyesuaian[$tglStr] ?? $kapasitasStandar;
        }

        $bebanTerpakai = Pesanan::whereIn('status_produksi', ['menunggu_bahan', 'antrean_cutting', 'proses_cutting'])
            ->where('approval_manajer', '!=', 'ditolak')
            ->where('tanggal_deadline', '<=', $akhir->format('Y-m-d'))
            ->sum('estimasi_jam_cutting');

        return [
            'total_kapasitas' => round($totalKapasitas, 2),
            'beban_terpakai'  => round($bebanTerpakai, 2),
            'sisa_kapasitas'  => round($totalKapasitas - $bebanTerpakai, 2),
        ];
    }

    public function deteksiSmartBatching()
    {
        return Pesanan::where('status_produksi', 'antrean_cutting')
            ->where('status_bahan', 'siap_potong')
            ->selectRaw('jenis_kain, COUNT(*) as total_order, SUM(jumlah_pcs) as total_pcs, SUM(estimasi_jam_cutting) as total_jam')
            ->groupBy('jenis_kain')
            ->having('total_order', '>', 1)
            ->get();
    }

    /**
     * Memprediksi tanggal selesai per tahapan (Cutting, Bordir, Sewing, QC, & Packing/Final)
     */
    public function prediksiTimelinePesanan(Pesanan $targetPesanan): array
    {
        $setting = PengaturanKapasitas::first();
        $kapasitasHarian = ($setting->jumlah_operator_cutting ?? 2) * ($setting->jam_kerja_per_hari ?? 8);
        if ($kapasitasHarian <= 0) $kapasitasHarian = 16;

        $status = $targetPesanan->status_produksi;

        // 1. Prediksi Selesai Cutting
        $jamAntrean = 0;
        if (in_array($status, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting'])) {
            $jamAntrean = Pesanan::whereIn('status_produksi', ['menunggu_bahan', 'antrean_cutting', 'proses_cutting'])
                ->where('approval_manajer', '!=', 'ditolak')
                ->where(function ($q) use ($targetPesanan) {
                    $q->where('urutan_prioritas', '<', $targetPesanan->urutan_prioritas)
                      ->orWhere(function ($q2) use ($targetPesanan) {
                          $q2->where('urutan_prioritas', $targetPesanan->urutan_prioritas)
                             ->where('id', '<=', $targetPesanan->id);
                      });
                })
                ->sum('estimasi_jam_cutting');

            $hariCutting = (int) ceil($jamAntrean / $kapasitasHarian);
            $tglSelesaiCutting = $this->tambahHariKerja(Carbon::today(), $hariCutting);
            $tglBerjalan = $tglSelesaiCutting->copy();
        } else {
            // Jika sudah melewati tahap cutting
            $tglSelesaiCutting = null;
            $tglBerjalan = Carbon::today();
        }

        // 2. Prediksi Kembali Vendor Bordir (Jika pakai bordir)
        $tglSelesaiBordir = null;
        if ($targetPesanan->pakai_bordir && in_array($status, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting', 'vendor_bordir'])) {
            if ($status === 'vendor_bordir' && $targetPesanan->vendorTerakhir?->tanggal_estimasi_kembali) {
                $tglSelesaiBordir = Carbon::parse($targetPesanan->vendorTerakhir->tanggal_estimasi_kembali);
            } else {
                $tglSelesaiBordir = $this->tambahHariKerja($tglBerjalan, 3); // Estimasi 3 hari kerja vendor
            }
            $tglBerjalan = $tglSelesaiBordir->copy();
        }

        // 3. Prediksi Selesai Sewing (Asumsi kapasitas jahit rata-rata 65 pcs/hari, minimal 1 hari)
        $tglSelesaiSewing = null;
        if (in_array($status, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting', 'vendor_bordir', 'sewing'])) {
            $hariSewing = max(1, (int) ceil($targetPesanan->jumlah_pcs / 65));
            $tglSelesaiSewing = $this->tambahHariKerja($tglBerjalan, $hariSewing);
            $tglBerjalan = $tglSelesaiSewing->copy();
        }

        // 4. Prediksi Selesai QC (Asumsi kapasitas pengecekan QC 150 pcs/hari, minimal 1 hari)
        $tglSelesaiQc = null;
        if (in_array($status, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting', 'vendor_bordir', 'sewing', 'qc'])) {
            $hariQc = max(1, (int) ceil($targetPesanan->jumlah_pcs / 150));
            $tglSelesaiQc = $this->tambahHariKerja($tglBerjalan, $hariQc);
            $tglBerjalan = $tglSelesaiQc->copy();
        }

        // 5. Prediksi Selesai Finishing & Packing / Final (1 hari kerja setelah QC)
        if ($status === 'selesai') {
            $tglPrediksiSelesai = Carbon::today();
        } else {
            $tglPrediksiSelesai = $this->tambahHariKerja($tglBerjalan, 1);
        }

        $deadline = Carbon::parse($targetPesanan->tanggal_deadline);
        $selisihHari = $tglPrediksiSelesai->diffInDays($deadline, false);

        return [
            'tgl_selesai_cutting'  => $tglSelesaiCutting ? $tglSelesaiCutting->format('d M Y') : 'Sudah Selesai',
            'tgl_selesai_bordir'   => $targetPesanan->pakai_bordir ? ($tglSelesaiBordir ? $tglSelesaiBordir->format('d M Y') : 'Sudah Selesai') : null,
            'tgl_selesai_sewing'   => $tglSelesaiSewing ? $tglSelesaiSewing->format('d M Y') : 'Sudah Selesai',
            'tgl_selesai_qc'       => $tglSelesaiQc ? $tglSelesaiQc->format('d M Y') : 'Sudah Selesai',
            'tgl_prediksi_selesai' => $tglPrediksiSelesai->format('d M Y'),
            'selisih_hari'         => $selisihHari,
            'status_prediksi'      => $selisihHari >= 0 ? 'ON TRACK' : 'RISIKO TELAT ' . abs($selisihHari) . ' HARI',
        ];
    }

    private function tambahHariKerja(Carbon $tanggalAwal, int $jumlahHari): Carbon
    {
        $hasil = $tanggalAwal->copy();
        $ditambah = 0;
        while ($ditambah < $jumlahHari) {
            $hasil->addDay();
            if (!$hasil->isSunday()) {
                $ditambah++;
            }
        }
        return $hasil;
    }
}