<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $table = 'transaksi_detail';
    protected $primaryKey = 'id_transaksi_detail';

    protected $fillable = [
        'id_transaksi',
        'periode_awal',
        'periode_akhir',
        'nominal',
        'nominal_per_bulan',
    ];

    protected $casts = [
        'nominal_per_bulan' => 'array', // supaya otomatis jadi array saat diakses
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }
}
