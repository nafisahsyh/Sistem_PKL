<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Petani extends Model
{
    use HasFactory;

    protected $table = 'petani';
    protected $primaryKey = 'id_petani';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomor_anggota_plasma',
        'nomor_anggota_koperasi',
        'NIK',
        'nama',
        'alamat',
        'status',
        'pdf_scan_ktp',
        'pdf_scan_kk',
    ];

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    public function kepemilikan()
    {
        return $this->hasMany(Kepemilikan::class, 'id_petani', 'id_petani');
    }

    public function riwayatSebagaiSebelum()
    {
        return $this->hasMany(RiwayatKepemilikan::class, 'id_petani_sebelum');
    }

    public function riwayatSebagaiSesudah()
    {
        return $this->hasMany(RiwayatKepemilikan::class, 'id_petani_sesudah');
    }


    // Event untuk menghapus file PDF otomatis
    protected static function booted()
    {
        static::deleting(function ($petani) {
            // Hapus file KTP
            if ($petani->pdf_scan_ktp && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_ktp)) {
                Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_ktp);
            }

            // Hapus file KK
            if ($petani->pdf_scan_kk && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_kk)) {
                Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_kk);
            }

            // Hapus semua kepemilikan terkait
            foreach ($petani->kepemilikan as $kepemilikan) {
                $kepemilikan->delete();
            }
        });
    }
}
