<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    protected $table = 'saldo';
    protected $primaryKey = 'id_saldo';

    protected $fillable = [
        'id_petani',
        'id_desa',
        'id_tahun_tanam',
        'bulan_awal',
        'bulan_akhir',
        'saldo',
        'saldo_awal'
    ];

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_petani', 'id_petani')
            ->whereColumn('transaksi.id_desa', 'saldo.id_desa')
            ->whereColumn('transaksi.id_tahun_tanam', 'saldo.id_tahun_tanam');
    }
}
