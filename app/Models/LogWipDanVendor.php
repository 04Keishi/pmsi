<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogWipDanVendor extends Model
{
    protected $table = 'log_wip_dan_vendor';

    protected $fillable = [
        'id_pesanan',
        'stasiun_tujuan',
        'nama_vendor',
        'tanggal_estimasi_kembali',
        'jumlah_pcs_diproses',
        'jumlah_pcs_reject',
        'waktu_scan',
        'dicatat_oleh'
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}