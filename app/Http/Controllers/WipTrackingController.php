<?php

namespace App\Http\Controllers;

use App\Models\{LogWipDanVendor, Pesanan};
use Illuminate\Http\Request;

class WipTrackingController extends Controller
{
    public function index()
    {
        $riwayatTerbaru = LogWipDanVendor::with('pesanan')->latest('waktu_scan')->take(10)->get();
        return view('wip.scan', compact('riwayatTerbaru'));
    }

    public function cariSpk(string $kodeSpk)
    {
        $pesanan = Pesanan::where('kode_spk', $kodeSpk)->first();
        if (!$pesanan) {
            return response()->json(['found' => false, 'message' => 'Kode SPK tidak ditemukan.'], 404);
        }
        return response()->json(['found' => true, 'pesanan' => $pesanan]);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'kode_spk'                 => 'required|exists:pesanan,kode_spk',
            'stasiun_tujuan'           => 'required|in:cutting,vendor_bordir,sewing,qc,finishing_packing,selesai',
            'nama_vendor'              => 'required_if:stasiun_tujuan,vendor_bordir|nullable|string|max:100',
            'tanggal_estimasi_kembali' => 'required_if:stasiun_tujuan,vendor_bordir|nullable|date',
            'jumlah_pcs_diproses'      => 'required|integer|min:1',
            'jumlah_pcs_reject'        => 'nullable|integer|min:0',
        ]);

        $pesanan = Pesanan::where('kode_spk', $validated['kode_spk'])->firstOrFail();

        $statusBaru = match ($validated['stasiun_tujuan']) {
            'cutting' => 'proses_cutting',
            default   => $validated['stasiun_tujuan'],
        };

        $pesanan->update(['status_produksi' => $statusBaru]);

        LogWipDanVendor::create([
            'id_pesanan'               => $pesanan->id,
            'stasiun_tujuan'           => $validated['stasiun_tujuan'],
            'nama_vendor'              => $validated['nama_vendor'] ?? null,
            'tanggal_estimasi_kembali' => $validated['tanggal_estimasi_kembali'] ?? null,
            'jumlah_pcs_diproses'      => $validated['jumlah_pcs_diproses'],
            'jumlah_pcs_reject'        => $validated['jumlah_pcs_reject'] ?? 0,
            'waktu_scan'               => now(),
            'dicatat_oleh'             => auth()->id(),
        ]);

        return back()->with('success', "Status {$pesanan->kode_spk} berhasil dipindahkan ke " . strtoupper(str_replace('_', ' ', $statusBaru)));
    }
}