<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MasterKompleksitas extends Model {
    protected $table = 'master_kompleksitas';
    protected $fillable = ['nama_level', 'estimasi_menit_per_pcs', 'deskripsi'];
}