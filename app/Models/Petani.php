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

    public function tahunTanam()
    {
        return $this->belongsTo(Tahun_Tanam::class, 'id_tahun_tanam');
    }

    public function kepemilikan()
    {
        return $this->hasMany(Kepemilikan::class, 'id_petani', 'id_petani');
    }

    public function riwayatSebagaiSebelum()
    {
        return $this->hasMany(RiwayatKepemilikan::class, 'id_petani_sebelum');
    }

    // app/Models/Petani.php

    public function detailKepemilikan()
    {
        return $this->hasManyThrough(
            DetailKepemilikan::class,
            Kepemilikan::class,
            'id_petani',
            'id_kepemilikan',
            'id_petani',
            'id_kepemilikan'
        );
    }

    public function riwayatSebagaiSesudah()
    {
        return $this->hasMany(RiwayatKepemilikan::class, 'id_petani_sesudah');
    }


    // Event untuk menghapus file PDF otomatis
    protected static function booted()
    {

        static::updated(function ($petani) {
            if ($petani->isDirty('status') && $petani->status === 'berhenti') {
                $petani->detailKepemilikan()->update(['status_kepemilikan' => 'nonaktif']);
            }
        });

        static::deleting(function ($petani) {
            if ($petani->pdf_scan_ktp && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_ktp)) {
                Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_ktp);
            }

            if ($petani->pdf_scan_kk && Storage::disk('public')->exists('ktp_pdf/' . $petani->pdf_scan_kk)) {
                Storage::disk('public')->delete('ktp_pdf/' . $petani->pdf_scan_kk);
            }

            foreach ($petani->kepemilikan as $kepemilikan) {
                $kepemilikan->delete();
            }
        });

        // Default status saat create
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
        // Jangan ubah status kalau petani berhenti
        // if (in_array($this->status, ['berhenti'])) {
        //     return;
        // }

        $jumlahLahanAktif = DetailKepemilikan::whereHas('kepemilikan', function ($q) {
            $q->where('id_petani', $this->id_petani);
        })->where('status_kepemilikan', 'aktif')->count();

        if ($jumlahLahanAktif === 0 && $this->status !== 'tidak_aktif') {
            $this->update(['status' => 'tidak_aktif']);
        } elseif ($jumlahLahanAktif > 0 && $this->status !== 'aktif') {
            $this->update(['status' => 'aktif']);
        }
    }

}
