<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\CuttingCapacityService;
use Illuminate\Http\Request;

class CuttingQueueController extends Controller
{
    public function index(CuttingCapacityService $service)
    {
        $siapPotong = Pesanan::with('kompleksitas')
            ->whereIn('status_produksi', ['antrean_cutting', 'proses_cutting'])
            ->where('approval_manajer', '!=', 'ditolak')
            ->orderBy('urutan_prioritas', 'asc')
            ->orderBy('tanggal_deadline', 'asc')
            ->get();

        $menungguBahan = Pesanan::with('kompleksitas')
            ->where('status_produksi', 'menunggu_bahan')
            ->orderBy('tanggal_deadline', 'asc')
            ->get();

        $batching = $service->deteksiSmartBatching();

        return view('cutting.queue', compact('siapPotong', 'menungguBahan', 'batching'));
    }

    public function updatePrioritas(Request $request, Pesanan $pesanan)
    {
        $request->validate(['urutan_prioritas' => 'required|integer|min:1']);
        $pesanan->update(['urutan_prioritas' => $request->urutan_prioritas]);
        return back()->with('success', 'Urutan prioritas cutting diperbarui.');
    }

    public function cetakSpk(Pesanan $pesanan)
    {
        return view('cutting.cetak-spk', compact('pesanan'));
    }
}