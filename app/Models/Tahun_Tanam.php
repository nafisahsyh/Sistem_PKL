<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tahun_Tanam extends Model
{
    use HasFactory;

    protected $table = 'tahun_tanam';
    protected $primaryKey = 'id_tahun_tanam';
    protected $fillable = ['tahun'];

    public $incrementing = true;

    // kalau BIGINT, Laravel sudah otomatis baca sebagai int (bukan string)
    protected $keyType = 'int';
}
