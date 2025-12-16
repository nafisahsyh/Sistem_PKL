<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_bagi_bulanan',
        'id_petani',
        'id_desa',
        'id_tahun_tanam',
        'tipe',
        'metode',
        'nominal',
        'tanggal',
        'keterangan',
        'no_urut',
        'no_bukti',
        'bulan_awal',
        'bulan_akhir',
    ];

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani');
    }
}
