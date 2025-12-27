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
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }
}
