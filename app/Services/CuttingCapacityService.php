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
     * Memprediksi tanggal selesai per tahapan & tanggal akhir pesanan berdasarkan antrean cutting riil
     */
    public function prediksiTimelinePesanan(Pesanan $targetPesanan): array
    {
        $setting = PengaturanKapasitas::first();
        $kapasitasHarian = ($setting->jumlah_operator_cutting ?? 2) * ($setting->jam_kerja_per_hari ?? 8);
        if ($kapasitasHarian <= 0) $kapasitasHarian = 16;

        // 1. Hitung akumulasi jam antrean cutting di depan pesanan ini + pesanan ini sendiri
        $jamAntrean = 0;
        if (in_array($targetPesanan->status_produksi, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting'])) {
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
        }

        // Konversi jam antrean cutting menjadi hari kerja (lewati hari Minggu)
        $hariCutting = (int) ceil($jamAntrean / $kapasitasHarian);
        $tglSelesaiCutting = $this->tambahHariKerja(Carbon::today(), $hariCutting);

        // 2. Hitung estimasi waktu setelah cutting (Bordir + Sewing + QC + Packing)
        $tglBerjalan = $tglSelesaiCutting->copy();

        // Jika sudah lewat cutting, mulai hitung dari hari ini berdasarkan posisi stasiunnya
        if (!in_array($targetPesanan->status_produksi, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting'])) {
            $tglBerjalan = Carbon::today();
        }

        $tglSelesaiBordir = null;
        if ($targetPesanan->pakai_bordir && in_array($targetPesanan->status_produksi, ['menunggu_bahan', 'antrean_cutting', 'proses_cutting', 'vendor_bordir'])) {
            // Jika sudah di vendor & ada tanggal estimasi kembali dari vendor, gunakan tanggal itu
            if ($targetPesanan->status_produksi === 'vendor_bordir' && $targetPesanan->vendorTerakhir?->tanggal_estimasi_kembali) {
                $tglSelesaiBordir = Carbon::parse($targetPesanan->vendorTerakhir->tanggal_estimasi_kembali);
            } else {
                $tglSelesaiBordir = $this->tambahHariKerja($tglBerjalan, 3); // Rata-rata vendor bordir 3 hari kerja
            }
            $tglBerjalan = $tglSelesaiBordir->copy();
        }

        // Estimasi Sewing + QC + Packing (asumsi kapasitas jahit & finishing rata-rata 50 pcs/hari, minimal 2 hari)
        $hariHilir = max(2, (int) ceil($targetPesanan->jumlah_pcs / 50));
        if ($targetPesanan->status_produksi === 'qc') $hariHilir = 1;
        if ($targetPesanan->status_produksi === 'finishing_packing') $hariHilir = 1;

        $tglPrediksiSelesai = $this->tambahHariKerja($tglBerjalan, $hariHilir);
        $deadline = Carbon::parse($targetPesanan->tanggal_deadline);
        $selisihHari = $tglPrediksiSelesai->diffInDays($deadline, false); // Positif = aman, Negatif = telat

        return [
            'tgl_selesai_cutting' => $tglSelesaiCutting->format('d M Y'),
            'tgl_selesai_bordir'  => $tglSelesaiBordir?->format('d M Y'),
            'tgl_prediksi_selesai'=> $tglPrediksiSelesai->format('d M Y'),
            'selisih_hari'        => $selisihHari,
            'status_prediksi'     => $selisihHari >= 0 ? 'ON TRACK' : 'RISIKO TELAT ' . abs($selisihHari) . ' HARI',
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