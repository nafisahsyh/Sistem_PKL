<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petani extends Model
{
    use HasFactory;

    protected $table = 'petani';
    protected $primaryKey = 'id_petani';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomor_anggota_plasma',
        'nomor_anggota_koperasi',
        'NIK',
        'nama',
        'alamat',
        'status',
        'pdf_scan_ktp',
        'pdf_scan_kk',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    public function kepemilikan()
    {
        return $this->hasMany(Kepemilikan::class, 'id_petani', 'id_petani');
    }
}

