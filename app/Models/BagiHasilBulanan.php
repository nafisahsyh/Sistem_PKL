<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagiHasilBulanan extends Model
{
    protected $table = 'bagi_hasil_bulanan';
    protected $primaryKey = 'id_bagi_bulanan';

    protected $fillable = [
        'id_desa',
        'id_tahun_tanam',
        'bulan',
        'tahun',
        'tanggal_bagi',
        'total_bagian',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam');
    }
}
