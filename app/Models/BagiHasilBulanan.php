<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagiHasilBulanan extends Model
{
    protected $table = 'bagi_hasil_bulanan';
    protected $primaryKey = 'id_bagi_bulanan';
    public $incrementing = true;    // <-- tambahkan ini
    protected $keyType = 'int';     // <-- tambahkan ini

    protected $fillable = [
        'id_desa',
        'id_tahun_tanam',
        'bulan',
        'tahun',
        'tanggal_bagi',
        'total_bagian',
        'luasan_total_snapshot',
        'sisa_saldo_snapshot',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa');
    }

    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam');
    }

    public function saldoLalu()
    {
        return $this->hasMany(
            SaldoLalu::class,
            'id_bagi_bulanan',
            'id_bagi_bulanan'
        );
    }
}
