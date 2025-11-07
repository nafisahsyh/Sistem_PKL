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
        'no_telepon',
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

        static::creating(function ($petani) {
            $petani->status = 'aktif';
        });
    }

    public function kepemilikanAktif()
    {
        return $this->hasMany(Kepemilikan::class, 'id_petani', 'id_petani')
            ->whereHas('detailKepemilikan', function ($q) {
                $q->where('status_kepemilikan', 'aktif');
            });
    }

    public function updateStatusPetani()
    {
        // Hitung total lahan yang dimiliki petani ini
        $jumlahLahan = \App\Models\DetailKepemilikan::whereHas('kepemilikan', function ($q) {
            $q->where('id_petani', $this->id_petani);
        })->count();

        // Kalau nggak punya lahan lagi, ubah ke tidak_aktif
        if ($jumlahLahan === 0 && $this->status !== 'tidak_aktif') {
            $this->update(['status' => 'tidak_aktif']);
        }

        // Kalau punya lahan, ubah ke aktif
        elseif ($jumlahLahan > 0 && $this->status !== 'aktif') {
            $this->update(['status' => 'aktif']);
        }
    }

}
