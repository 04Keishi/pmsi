<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom role ke tabel users bawaan Breeze
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manajer'])->default('admin')->after('email');
        });

        // 1. Pengaturan Kapasitas Standar Cutting
        Schema::create('pengaturan_kapasitas', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah_operator_cutting')->default(2);
            $table->decimal('jam_kerja_per_hari', 4, 2)->default(8.00);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Kalender Pengecualian Harian (Lembur / Operator Absen)
        Schema::create('penyesuaian_kapasitas_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->decimal('kapasitas_jam_khusus', 5, 2);
            $table->string('keterangan', 150);
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        // 3. Master Kompleksitas Pola
        Schema::create('master_kompleksitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_level', 50);
            $table->integer('estimasi_menit_per_pcs');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 4. Pesanan Utama (Orders)
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_spk', 30)->unique();
            $table->string('nama_pelanggan', 100);
            $table->string('model_pakaian', 100);
            $table->string('jenis_kain', 50);
            $table->integer('jumlah_pcs');
            $table->foreignId('id_kompleksitas')->constrained('master_kompleksitas');
            $table->boolean('pakai_bordir')->default(false);
            $table->enum('status_bahan', ['belum_siap', 'siap_potong'])->default('belum_siap');
            $table->decimal('estimasi_jam_cutting', 6, 2);
            $table->string('catatan_estimasi')->nullable();
            $table->date('tanggal_order');
            $table->date('tanggal_deadline');
            $table->enum('status_produksi', [
                'menunggu_bahan', 'antrean_cutting', 'proses_cutting',
                'vendor_bordir', 'sewing', 'qc', 'finishing_packing', 'selesai'
            ])->default('menunggu_bahan');
            $table->integer('urutan_prioritas')->default(99);
            $table->boolean('is_rush_order')->default(false);
            $table->enum('approval_manajer', ['tidak_perlu', 'menunggu', 'disetujui', 'ditolak'])->default('tidak_perlu');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // 5. Log Perpindahan Divisi (WIP) & Tracking Vendor Bordir
        Schema::create('log_wip_dan_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pesanan')->constrained('pesanan')->cascadeOnDelete();
            $table->enum('stasiun_tujuan', ['cutting', 'vendor_bordir', 'sewing', 'qc', 'finishing_packing', 'selesai']);
            $table->string('nama_vendor', 100)->nullable();
            $table->date('tanggal_estimasi_kembali')->nullable();
            $table->integer('jumlah_pcs_diproses');
            $table->integer('jumlah_pcs_reject')->default(0);
            $table->dateTime('waktu_scan')->useCurrent();
            $table->foreignId('dicatat_oleh')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_wip_dan_vendor');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('master_kompleksitas');
        Schema::dropIfExists('penyesuaian_kapasitas_harian');
        Schema::dropIfExists('pengaturan_kapasitas');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};