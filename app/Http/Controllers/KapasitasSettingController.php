<?php

namespace App\Http\Controllers;

use App\Models\{MasterKompleksitas, PengaturanKapasitas, PenyesuaianKapasitasHarian};
use Illuminate\Http\Request;

class KapasitasSettingController extends Controller
{
    public function index()
    {
        $setting = PengaturanKapasitas::first();
        $penyesuaian = PenyesuaianKapasitasHarian::orderBy('tanggal', 'desc')->get();
        $kompleksitas = MasterKompleksitas::all();

        return view('kapasitas.index', compact('setting', 'penyesuaian', 'kompleksitas'));
    }

    public function updateStandar(Request $request)
    {
        $request->validate([
            'jumlah_operator_cutting' => 'required|integer|min:1',
            'jam_kerja_per_hari'      => 'required|numeric|min:1|max:24',
        ]);

        PengaturanKapasitas::updateOrCreate(['id' => 1], [
            'jumlah_operator_cutting' => $request->jumlah_operator_cutting,
            'jam_kerja_per_hari'      => $request->jam_kerja_per_hari,
            'updated_by'              => auth()->id(),
        ]);

        return back()->with('success', 'Kapasitas standar cutting berhasil diperbarui.');
    }

    public function storePenyesuaian(Request $request)
    {
        $request->validate([
            'tanggal'              => 'required|date',
            'kapasitas_jam_khusus' => 'required|numeric|min:0|max:48',
            'keterangan'           => 'required|string|max:150',
        ]);

        PenyesuaianKapasitasHarian::updateOrCreate(
            ['tanggal' => $request->tanggal],
            [
                'kapasitas_jam_khusus' => $request->kapasitas_jam_khusus,
                'keterangan'           => $request->keterangan,
                'updated_by'           => auth()->id(),
            ]
        );

        return back()->with('success', 'Penyesuaian jam harian (lembur/absen) berhasil disimpan.');
    }

    public function storeKompleksitas(Request $request)
    {
        $request->validate([
            'nama_level'             => 'required|string|max:50',
            'estimasi_menit_per_pcs' => 'required|integer|min:1',
            'deskripsi'              => 'nullable|string',
        ]);

        MasterKompleksitas::create($request->only('nama_level', 'estimasi_menit_per_pcs', 'deskripsi'));
        return back()->with('success', 'Kategori kompleksitas pola ditambahkan.');
    }
}