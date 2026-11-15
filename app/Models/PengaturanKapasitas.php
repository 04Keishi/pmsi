<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PengaturanKapasitas extends Model {
    protected $table = 'pengaturan_kapasitas';
    protected $fillable = ['jumlah_operator_cutting', 'jam_kerja_per_hari', 'updated_by'];
}