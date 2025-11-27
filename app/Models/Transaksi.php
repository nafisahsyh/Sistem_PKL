<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_petani',
        'tipe',
        'metode',
        'nominal',
        'tanggal',
        'keterangan',
    ];

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani');
    }
}
