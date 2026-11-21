<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_detail_ukuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pesanan')->constrained('pesanan')->cascadeOnDelete();
            $table->string('ukuran', 15); // S, M, L, XL, XXL, Custom
            $table->integer('jumlah_pcs');
            $table->decimal('faktor_pengali', 4, 2)->default(1.00); // Misal: S=0.95, M=1.00, L=1.05, XL=1.15, XXL=1.25
            $table->decimal('estimasi_jam_ukuran', 6, 2);
            $table->timestamps();
        });

        // Tambahkan kolom simpan waktu setup ganti pola di tabel pesanan
        Schema::table('pesanan', function (Blueprint $table) {
            $table->decimal('waktu_setup_pola', 5, 2)->default(0)->after('estimasi_jam_cutting');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('waktu_setup_pola');
        });
        Schema::dropIfExists('pesanan_detail_ukuran');
    }
};