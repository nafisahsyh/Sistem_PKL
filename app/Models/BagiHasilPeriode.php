<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagiHasilPeriode extends Model
{
    protected $table = 'bagi_hasil_periode';
    protected $primaryKey = 'id_bagi_periode';

    protected $fillable = [
        'id_desa',
        'id_tahun_tanam',
        'bulan_awal',
        'bulan_akhir',
        'tahun',
        'tanggal_bagi',
        'total_periode',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam');
    }

    public function petaniPeriode()
    {
        return $this->hasMany(BagiHasilPetani::class, 'id_bagi_periode');
    }
}
