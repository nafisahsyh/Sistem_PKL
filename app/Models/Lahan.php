<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    // Nama tabel di database
    protected $table = 'lahan';
    protected $primaryKey = 'id_lahan';
    public $timestamps = false; // karena di migrasi tidak ada created_at dan updated_at

    // Field yang bisa diisi
    protected $fillable = [
        'id_desa',
        'id_tahun_tanam',
        'luas_peta',
    ];

    // Relasi ke tabel Desa
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    // Relasi ke tabel TahunTanam
    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam', 'id_tahun_tanam');
    }

    // Relasi ke tabel Kepemilikan
    public function kepemilikan()
    {
        return $this->hasMany(Kepemilikan::class, 'id_lahan', 'id_lahan');
    }
}
