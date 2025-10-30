<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class DetailKepemilikan extends Model
{
    protected $table = 'detail_kepemilikan';
    protected $primaryKey = 'id_detail_kepemilikan';
    protected $fillable = [
        'id_kepemilikan',
        'id_lahan',
        'nomor_SHM',
        'nama_SHM',
        'nomor_sporadik',
        'nama_sporadik',
        'nomor_kavling',
        'luas_surat',
        'nomor_pbb',
        'jumlah_pbb',
        'pdf_scan_shm',
        'pdf_scan_peta',
        'status_kepemilikan',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    public function kepemilikan()
    {
        return $this->belongsTo(Kepemilikan::class, 'id_kepemilikan', 'id_kepemilikan');
    }

    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }

    public function pbb()
    {
        return $this->hasMany(Pbb::class, 'id_detail_kepemilikan');
    }

    // Event untuk menghapus file ketika detail dihapus
    protected static function booted()
    {
        static::deleting(function ($detail) {
            if ($detail->pdf_scan_shm && Storage::disk('public')->exists($detail->pdf_scan_shm)) {
                Storage::disk('public')->delete($detail->pdf_scan_shm);
            }
            if ($detail->pdf_scan_peta && Storage::disk('public')->exists($detail->pdf_scan_peta)) {
                Storage::disk('public')->delete($detail->pdf_scan_peta);
            }
        });
    }
}