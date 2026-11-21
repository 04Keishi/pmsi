<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\CuttingCapacityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(CuttingCapacityService $capacityService)
    {
        $kapasitasMingguIni = $capacityService->hitungKapasitasTersedia(Carbon::today()->addDays(6)->format('Y-m-d'));

        $pesananAktif = Pesanan::with(['kompleksitas', 'vendorTerakhir'])
            ->where('status_produksi', '!=', 'selesai')
            ->where('approval_manajer', '!=', 'ditolak')
            ->orderBy('urutan_prioritas', 'asc')
            ->orderBy('tanggal_deadline', 'asc')
            ->get()
            ->map(function ($item) use ($capacityService) {
                $item->prediksi = $capacityService->prediksiTimelinePesanan($item);
                return $item;
            });

        // Query Riwayat SPK yang Sudah Selesai
        $pesananSelesai = Pesanan::with(['kompleksitas', 'logs'])
            ->where('status_produksi', 'selesai')
            ->orderBy('updated_at', 'desc')
            ->get();

        $rushOrdersMenunggu = Pesanan::where('is_rush_order', true)
            ->where('approval_manajer', 'menunggu')
            ->get();

        $stats = [
            'antrean_cutting' => $pesananAktif->whereIn('status_produksi', ['antrean_cutting', 'proses_cutting'])->count(),
            'di_vendor'       => $pesananAktif->where('status_produksi', 'vendor_bordir')->count(),
            'berisiko_telat'  => $pesananAktif->filter(fn($p) => $p->prediksi['selisih_hari'] < 0)->count(),
        ];

        return view('dashboard', compact('kapasitasMingguIni', 'pesananAktif', 'pesananSelesai', 'rushOrdersMenunggu', 'stats'));
    }

    public function keputusanRushOrder(Request $request, Pesanan $pesanan)
    {
        $request->validate(['keputusan' => 'required|in:disetujui,ditolak']);
        $pesanan->update([
            'approval_manajer' => $request->keputusan,
            'urutan_prioritas' => $request->keputusan === 'disetujui' ? 1 : $pesanan->urutan_prioritas,
        ]);

        return back()->with('success', 'Status Rush Order berhasil diperbarui.');
    }
}