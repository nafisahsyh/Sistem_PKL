<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    protected $primaryKey = 'id_kecamatan';
    protected $fillable = ['kecamatan'];

    public $incrementing = true;

    // kalau BIGINT, Laravel sudah otomatis baca sebagai int (bukan string)
    protected $keyType = 'int';
}
