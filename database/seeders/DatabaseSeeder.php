<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, PengaturanKapasitas, MasterKompleksitas, Pesanan};
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $manajer = User::create([
            'name' => 'Budi Santoso (Manajer)',
            'email' => 'manajer@agrakreasi.com',
            'password' => Hash::make('password'),
            'role' => 'manajer',
        ]);

        $admin = User::create([
            'name' => 'Siti Aminah (Admin Produksi)',
            'email' => 'admin@agrakreasi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        PengaturanKapasitas::create([
            'jumlah_operator_cutting' => 2,
            'jam_kerja_per_hari' => 8.00,
            'updated_by' => $manajer->id,
        ]);

        $lv1 = MasterKompleksitas::create(['nama_level' => 'Level 1 - Standar', 'estimasi_menit_per_pcs' => 3, 'deskripsi' => 'Kaos oblong, pola dasar sederhana']);
        $lv2 = MasterKompleksitas::create(['nama_level' => 'Level 2 - Menengah', 'estimasi_menit_per_pcs' => 7, 'deskripsi' => 'Kemeja kerja, polo shirt, celana standar']);
        $lv3 = MasterKompleksitas::create(['nama_level' => 'Level 3 - Rumit / Presisi', 'estimasi_menit_per_pcs' => 12, 'deskripsi' => 'Kain licin, motif batik sambung presisi, jaket banyak panel']);

        // Contoh 3 Pesanan Awal (2 dengan kain sama untuk demo Smart Batching)
        Pesanan::create([
            'kode_spk' => 'SPK-202611-001',
            'nama_pelanggan' => 'PT Maju Jaya',
            'model_pakaian' => 'Kemeja Seragam Lapangan',
            'jenis_kain' => 'American Drill Navy',
            'jumlah_pcs' => 60,
            'id_kompleksitas' => $lv2->id,
            'pakai_bordir' => true,
            'status_bahan' => 'siap_potong',
            'estimasi_jam_cutting' => 7.00,
            'tanggal_order' => Carbon::today()->subDays(2),
            'tanggal_deadline' => Carbon::today()->addDays(4),
            'status_produksi' => 'antrean_cutting',
            'urutan_prioritas' => 1,
            'created_by' => $admin->id,
        ]);

        Pesanan::create([
            'kode_spk' => 'SPK-202611-002',
            'nama_pelanggan' => 'CV Karya Mandiri',
            'model_pakaian' => 'Celana Kerja Teknisi',
            'jenis_kain' => 'American Drill Navy',
            'jumlah_pcs' => 40,
            'id_kompleksitas' => $lv2->id,
            'pakai_bordir' => false,
            'status_bahan' => 'siap_potong',
            'estimasi_jam_cutting' => 4.67,
            'tanggal_order' => Carbon::today()->subDay(),
            'tanggal_deadline' => Carbon::today()->addDays(5),
            'status_produksi' => 'antrean_cutting',
            'urutan_prioritas' => 2,
            'created_by' => $admin->id,
        ]);

        Pesanan::create([
            'kode_spk' => 'SPK-202611-003',
            'nama_pelanggan' => 'Komunitas Sepeda Bandung',
            'model_pakaian' => 'Jersey Custom Full Print',
            'jenis_kain' => 'Dryfit Milano',
            'jumlah_pcs' => 100,
            'id_kompleksitas' => $lv3->id,
            'pakai_bordir' => false,
            'status_bahan' => 'belum_siap',
            'estimasi_jam_cutting' => 20.00,
            'tanggal_order' => Carbon::today(),
            'tanggal_deadline' => Carbon::today()->addDays(7),
            'status_produksi' => 'menunggu_bahan',
            'urutan_prioritas' => 3,
            'created_by' => $admin->id,
        ]);
    }
}