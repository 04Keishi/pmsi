<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananDetailUkuran extends Model
{
    protected $table = 'pesanan_detail_ukuran';
    protected $fillable = ['id_pesanan', 'ukuran', 'jumlah_pcs', 'faktor_pengali', 'estimasi_jam_ukuran'];
}