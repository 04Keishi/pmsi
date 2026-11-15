<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PenyesuaianKapasitasHarian extends Model {
    protected $table = 'penyesuaian_kapasitas_harian';
    protected $fillable = ['tanggal', 'kapasitas_jam_khusus', 'keterangan', 'updated_by'];
}