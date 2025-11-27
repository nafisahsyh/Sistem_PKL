<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagiHasilPetani extends Model
{
    protected $table = 'bagi_hasil_petani';
    protected $primaryKey = 'id_bagi_petani';

    protected $fillable = [
        'id_bagi_periode',
        'id_petani',
        'total_luas_ksm',
        'total_nominal',
    ];

    public function periode()
    {
        return $this->belongsTo(BagiHasilPeriode::class, 'id_bagi_periode');
    }

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani');
    }
}
