<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoLalu extends Model
{
    protected $table = 'saldo_lalu';

    protected $primaryKey = 'id_saldo_lalu';

    public $timestamps = false;

    protected $fillable = [
        'id_bagi_bulanan',
        'id_petani',
        'id_desa',
        'id_tahun_tanam',
        'saldo_lalu',
        'created_at',
    ];

    //Relasi
    public function bagiBulanan()
    {
        return $this->belongsTo(
            BagiHasilBulanan::class,
            'id_bagi_bulanan',
            'id_bagi_bulanan'
        );
    }

    public function petani()
    {
        return $this->belongsTo(
            Petani::class,
            'id_petani',
            'id_petani'
        );
    }

    public function desa()
    {
        return $this->belongsTo(
            Desa::class,
            'id_desa',
            'id_desa'
        );
    }

    public function tahunTanam()
    {
        return $this->belongsTo(
            Tahun_Tanam::class,
            'id_tahun_tanam',
            'id_tahun_tanam'
        );
    }

    public function bagiHasilBulanan()
{
    return $this->belongsTo(BagiHasilBulanan::class, 'id_bagi_bulanan');
}

}
