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
        'nomor_sporadik',
        'luas_surat',
        'nomor_pbb',
        'jumlah_pbb',
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
