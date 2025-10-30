<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKepemilikan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_kepemilikan';
    protected $primaryKey = 'id_riwayat';
    protected $fillable = [
        'id_lahan',
        'id_petani_sebelum',
        'id_petani_sesudah',
        'tanggal_ganti',
        'keterangan'
    ];

    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan');
    }

    public function petaniSebelum()
    {
        return $this->belongsTo(Petani::class, 'id_petani_sebelum');
    }

    public function petaniSesudah()
    {
        return $this->belongsTo(Petani::class, 'id_petani_sesudah');
    }
}
