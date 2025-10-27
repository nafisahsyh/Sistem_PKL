<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kepemilikan extends Model
{
    protected $table = 'kepemilikan';
    protected $primaryKey = 'id_kepemilikan';
    protected $fillable = [
        'id_petani',
    ];

    protected $with = ['detailKepemilikan.lahan', 'petani']; // auto load

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'id_petani', 'id_petani');
    }

    public function detailKepemilikan()
    {
        return $this->hasMany(DetailKepemilikan::class, 'id_kepemilikan', 'id_kepemilikan');
    }
}
