<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagiHasilPetani extends Model
{
    protected $table = 'bagi_hasil_petani';
    protected $primaryKey = 'id_bagi_petani';

    protected $fillable = [
        'id_bagi_bulanan',
        'id_petani',
        'id_lahan',
        'id_desa',
        'id_tahun_tanam',
        'total_luas_ksm',
        'total_nominal',

        // snapshot fields
        'id_petani_snapshot',
        'id_desa_snapshot',
        'nama_petani_snapshot',
        'nik_petani_snapshot',
        'alamat_petani_snapshot',
        'nomor_plasma_snapshot',
        'nomor_koperasi_snapshot',
    ];


    public function bulanan()
    {
        return $this->belongsTo(BagiHasilBulanan::class, 'id_bagi_bulanan');
    }

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani');
    }

    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam');
    }
}
