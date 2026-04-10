<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    use HasFactory;

    protected $table = 'desa';
    protected $primaryKey = 'id_desa';
    protected $fillable = ['desa', 'id_kecamatan'];

    public $incrementing = true;
    protected $keyType = 'int';

    // Relasi ke kecamatan
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }

    public function lahan()
    {
        return $this->hasMany(Lahan::class, 'id_desa', 'id_desa');
    }
}
