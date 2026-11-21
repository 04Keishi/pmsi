<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model {
    protected $table = 'pesanan';
    protected $fillable = [
        'kode_spk', 'nama_pelanggan', 'model_pakaian', 'jenis_kain', 'jumlah_pcs',
        'id_kompleksitas', 'pakai_bordir', 'status_bahan', 'estimasi_jam_cutting',
        'catatan_estimasi', 'tanggal_order', 'tanggal_deadline', 'status_produksi',
        'urutan_prioritas', 'is_rush_order', 'approval_manajer', 'created_by', 'waktu_setup_pola'
    ];

    public function kompleksitas() {
        return $this->belongsTo(MasterKompleksitas::class, 'id_kompleksitas');
    }

    public function logs() {
        return $this->hasMany(LogWipDanVendor::class, 'id_pesanan')->latest('waktu_scan');
    }

    public function vendorTerakhir() {
        return $this->hasOne(LogWipDanVendor::class, 'id_pesanan')
            ->where('stasiun_tujuan', 'vendor_bordir')
            ->latest('waktu_scan');
    }

    public function detailUkuran() {
    return $this->hasMany(PesananDetailUkuran::class, 'id_pesanan');
    }
}