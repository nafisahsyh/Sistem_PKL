<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InisiasiSaldo extends Model
{
    use HasFactory;

    protected $table = 'inisiasi_saldo';

    protected $primaryKey = 'id_inisiasi_saldo';

    protected $fillable = [
        'id_petani',
        'id_desa',
        'id_tahun_tanam',
        'nomor_plasma',
        'nomor_koperasi',
        'nama_petani',
        'desa',
        'tahun_tanam',
    ];
}
