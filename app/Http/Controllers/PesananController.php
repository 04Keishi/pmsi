<?php

namespace App\Http\Controllers;

use App\Models\{MasterKompleksitas, Pesanan};
use App\Services\CuttingCapacityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function create()
    {
        $kompleksitas = MasterKompleksitas::all();
        return view('pesanan.create', compact('kompleksitas'));
    }

    public function cekKapasitasAjax(Request $request, CuttingCapacityService$service)
    {
        $request->validate([
            'tanggal_deadline' => 'required|date',
            'estimasi_jam'     => 'required|numeric|min:0.1',
        ]);

        $data =$service->hitungKapasitasTersedia($request->tanggal_deadline);$layak = $data['sisa_kapasitas'] >=$request->estimasi_jam;

        return response()->json([
            'layak'           => $layak,
            'total_kapasitas' => $data['total_kapasitas'],
            'beban_terpakai'  => $data['beban_terpakai'],
            'sisa_kapasitas'  => $data['sisa_kapasitas'],
            'selisih'         => round($data['sisa_kapasitas'] -$request->estimasi_jam, 2),
        ]);
    }

    public function store(Request $request)
    {
        $validated =$request->validate([
            'nama_pelanggan'       => 'required|string|max:100',
            'model_pakaian'        => 'required|string|max:100',
            'jenis_kain'           => 'required|string|max:50',
            'jumlah_pcs'           => 'required|integer|min:1',
            'id_kompleksitas'      => 'required|exists:master_kompleksitas,id',
            'status_bahan'         => 'required|in:belum_siap,siap_potong',
            'estimasi_jam_cutting' => 'required|numeric|min:0.1',
            'catatan_estimasi'     => 'nullable|string|max:255',
            'tanggal_deadline'     => 'required|date|after_or_equal:today',
        ]);

        $kodeSpk = 'SPK-' . date('Ym') . '-' . str_pad(Pesanan::count() + 1, 3, '0', STR_PAD_LEFT);
        $isRush =$request->has('is_rush_order');

        Pesanan::create([
            'kode_spk'             => $kodeSpk,
            'nama_pelanggan'       => $validated['nama_pelanggan'],
            'model_pakaian'        => $validated['model_pakaian'],
            'jenis_kain'           => $validated['jenis_kain'],
            'jumlah_pcs'           => $validated['jumlah_pcs'],
            'id_kompleksitas'      => $validated['id_kompleksitas'],
            'pakai_bordir'         => $request->has('pakai_bordir'),
            'status_bahan'         => $validated['status_bahan'],
            'estimasi_jam_cutting' => $validated['estimasi_jam_cutting'],
            'catatan_estimasi'     => $validated['catatan_estimasi'],
            'tanggal_order'        => Carbon::today(),
            'tanggal_deadline'     => $validated['tanggal_deadline'],
            'status_produksi'      => $validated['status_bahan'] === 'siap_potong' ? 'antrean_cutting' : 'menunggu_bahan',
            'is_rush_order'        => $isRush,
            'approval_manajer'     => $isRush ? 'menunggu' : 'tidak_perlu',
            'created_by'           => auth()->id(),
        ]);

        return redirect()->route('cutting.queue')->with('success', "Pesanan $kodeSpk berhasil dibuat.");
    }

    public function tandaiSiapPotong(Pesanan $pesanan)
    {
        $pesanan->update([
            'status_bahan'    => 'siap_potong',
            'status_produksi' => 'antrean_cutting',
        ]);

        return back()->with('success', "Bahan untuk {$pesanan->kode_spk} siap potong dan masuk antrean cutting.");
    }
}