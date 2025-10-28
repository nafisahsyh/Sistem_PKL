<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailKepemilikan extends Model
{
    protected $table = 'detail_kepemilikan';
    protected $primaryKey = 'id_detail_kepemilikan';
    protected $fillable = [
        'id_kepemilikan',
        'id_lahan',
        'nomor_SHM',
        'nama_SHM',
        'nomor_sporadik',
        'nama_sporadik',
        'nomor_kavling',
        'luas_surat',
        'nomor_pbb',
        'jumlah_pbb',
        'pdf_scan_shm',
        'pdf_scan_peta',
        'status_kepemilikan',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    public function kepemilikan()
    {
        return $this->belongsTo(Kepemilikan::class, 'id_kepemilikan', 'id_kepemilikan');
    }

    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }
}
