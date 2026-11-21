<?php

namespace App\Http\Controllers;

use App\Models\{MasterKompleksitas, Pesanan, PesananDetailUkuran};
use App\Services\CuttingCapacityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    // Faktor pengali waktu potong berdasarkan besar kecilnya pola ukuran
    private array $faktorUkuran = [
        'S'   => 0.95,
        'M'   => 1.00,
        'L'   => 1.05,
        'XL'  => 1.15,
        'XXL' => 1.25,
    ];

    public function create()
    {
        $kompleksitas = MasterKompleksitas::all();
        $faktorUkuran = $this->faktorUkuran;
        return view('pesanan.create', compact('kompleksitas', 'faktorUkuran'));
    }

    public function show(Pesanan $pesanan, CuttingCapacityService $service)
    {
        $pesanan->load(['kompleksitas', 'detailUkuran', 'logs']);
        $prediksi = $service->prediksiTimelinePesanan($pesanan);
        return view('pesanan.show', compact('pesanan', 'prediksi'));
    }

    public function cekKapasitasAjax(Request $request, CuttingCapacityService $service)
    {
        $request->validate([
            'tanggal_deadline' => 'required|date',
            'estimasi_jam'     => 'required|numeric|min:0.1',
        ]);

        $data = $service->hitungKapasitasTersedia($request->tanggal_deadline);
        $layak = $data['sisa_kapasitas'] >= $request->estimasi_jam;

        return response()->json([
            'layak'           => $layak,
            'total_kapasitas' => $data['total_kapasitas'],
            'beban_terpakai'  => $data['beban_terpakai'],
            'sisa_kapasitas'  => $data['sisa_kapasitas'],
            'selisih'         => round($data['sisa_kapasitas'] - $request->estimasi_jam, 2),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan'       => 'required|string|max:100',
            'model_pakaian'        => 'required|string|max:100',
            'jenis_kain'           => 'required|string|max:50',
            'id_kompleksitas'      => 'required|exists:master_kompleksitas,id',
            'status_bahan'         => 'required|in:belum_siap,siap_potong',
            'estimasi_jam_cutting' => 'required|numeric|min:0.1',
            'waktu_setup_pola'     => 'required|numeric|min:0',
            'catatan_estimasi'     => 'nullable|string|max:255',
            'tanggal_deadline'     => 'required|date|after_or_equal:today',
            'qty_ukuran'           => 'required|array',
            'qty_ukuran.*'         => 'nullable|integer|min:0',
        ]);

        // Filter ukuran yang diisi > 0 pcs
        $ukuranAktif = array_filter($validated['qty_ukuran'], fn($qty) => (int)$qty > 0);
        if (count($ukuranAktif) === 0) {
            return back()->withErrors(['qty_ukuran' => 'Minimal isi jumlah pcs pada salah satu ukuran (S/M/L/XL/XXL).'])->withInput();
        }

        $totalPcs = array_sum($ukuranAktif);
        $kompleksitas = MasterKompleksitas::findOrFail($validated['id_kompleksitas']);

        DB::transaction(function () use ($validated, $ukuranAktif, $totalPcs, $kompleksitas, $request) {
            $kodeSpk = 'SPK-' . date('Ym') . '-' . str_pad(Pesanan::count() + 1, 3, '0', STR_PAD_LEFT);
            $isRush = $request->has('is_rush_order');

            $pesanan = Pesanan::create([
                'kode_spk'             => $kodeSpk,
                'nama_pelanggan'       => $validated['nama_pelanggan'],
                'model_pakaian'        => $validated['model_pakaian'],
                'jenis_kain'           => $validated['jenis_kain'],
                'jumlah_pcs'           => $totalPcs,
                'id_kompleksitas'      => $validated['id_kompleksitas'],
                'pakai_bordir'         => $request->has('pakai_bordir'),
                'status_bahan'         => $validated['status_bahan'],
                'estimasi_jam_cutting' => $validated['estimasi_jam_cutting'],
                'waktu_setup_pola'     => $validated['waktu_setup_pola'],
                'catatan_estimasi'     => $validated['catatan_estimasi'],
                'tanggal_order'        => Carbon::today(),
                'tanggal_deadline'     => $validated['tanggal_deadline'],
                'status_produksi'      => $validated['status_bahan'] === 'siap_potong' ? 'antrean_cutting' : 'menunggu_bahan',
                'is_rush_order'        => $isRush,
                'approval_manajer'     => $isRush ? 'menunggu' : 'tidak_perlu',
                'created_by'           => auth()->id(),
            ]);

            foreach ($ukuranAktif as $ukuran => $qty) {
                $pengali = $this->faktorUkuran[$ukuran] ?? 1.00;
                $jamUkuran = round(($qty * $kompleksitas->estimasi_menit_per_pcs * $pengali) / 60, 2);

                PesananDetailUkuran::create([
                    'id_pesanan'          => $pesanan->id,
                    'ukuran'              => $ukuran,
                    'jumlah_pcs'          => $qty,
                    'faktor_pengali'      => $pengali,
                    'estimasi_jam_ukuran' => $jamUkuran,
                ]);
            }
        });

        return redirect()->route('cutting.queue')->with('success', 'Pesanan beserta rincian ukuran berhasil disimpan.');
    }

    public function tandaiSiapPotong(Pesanan $pesanan)
    {
        $pesanan->update([
            'status_bahan'    => 'siap_potong',
            'status_produksi' => 'antrean_cutting',
        ]);

        return back()->with('success', "Bahan untuk {$pesanan->kode_spk} siap potong.");
    }
}