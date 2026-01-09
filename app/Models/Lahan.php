<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Lahan extends Model
{
    protected $table = 'lahan';
    protected $primaryKey = 'id_lahan';
    public $timestamps = false;

    // Field yang bisa diisi
    protected $fillable = [
        'id_desa',
        'id_tahun_tanam',
        'luas_peta',
    ];

    // Relasi ke tabel Desa
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    // Relasi ke tabel TahunTanam
    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam', 'id_tahun_tanam');
    }

    // Relasi ke tabel Kepemilikan
    public function kepemilikan()
    {
        return $this->hasMany(Kepemilikan::class, 'id_lahan', 'id_lahan');
    }

    public function detailKepemilikan()
    {
        return $this->hasMany(DetailKepemilikan::class, 'id_lahan', 'id_lahan');
    }

    public function riwayatKepemilikan()
    {
        return $this->hasMany(RiwayatKepemilikan::class, 'id_lahan', 'id_lahan')
            ->orderBy('tanggal_ganti', 'asc');
    }

    // Event untuk menghapus file SHM dan Peta saat data Lahan dihapus
    protected static function booted()
    {
        static::deleting(function ($lahan) {
            foreach ($lahan->detailKepemilikan as $detail) {
                // Hapus file SHM
                if ($detail->pdf_scan_shm && Storage::disk('public')->exists('shm_pdf/' . $detail->pdf_scan_shm)) {
                    Storage::disk('public')->delete('shm_pdf/' . $detail->pdf_scan_shm);
                }

                // Hapus file Peta
                if ($detail->pdf_scan_peta && Storage::disk('public')->exists('peta_pdf/' . $detail->pdf_scan_peta)) {
                    Storage::disk('public')->delete('peta_pdf/' . $detail->pdf_scan_peta);
                }

                // Hapus detail kepemilikan
                $detail->delete();
            }
        });
    }
}
