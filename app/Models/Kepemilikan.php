<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kepemilikan extends Model
{
    protected $table = 'kepemilikan';
    protected $primaryKey = 'id_kepemilikan';
    public $timestamps = false; // karena tabel tidak memiliki kolom created_at & updated_at

    protected $fillable = [
        'id_petani',
        'id_lahan',
        'nomor_SHM',
        'nomor_sporadik',
        'luas_surat',
        'nomor_pbb',
        'jumlah_pbb',
        'status_kepemilikan',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * Relasi ke model Petani
     * Setiap kepemilikan dimiliki oleh satu petani
     */
    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani', 'id_petani');
    }

    /**
     * Relasi ke model Lahan
     * Setiap kepemilikan terkait dengan satu lahan
     */
    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }
}
