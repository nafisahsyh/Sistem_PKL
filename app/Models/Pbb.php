<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pbb extends Model
{
    use HasFactory;

    protected $table = 'pbb';
    protected $primaryKey = 'id_pbb';
    protected $fillable = ['id_detail_kepemilikan', 'tahun', 'jumlah', 'status'];

    public function detailKepemilikan()
    {
        return $this->belongsTo(DetailKepemilikan::class, 'id_detail_kepemilikan');
    }
}
