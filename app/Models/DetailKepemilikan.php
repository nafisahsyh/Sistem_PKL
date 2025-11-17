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
        'kode_lahan',
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
        'status_pengelolaan',

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
        // Setelah detail dibuat
        static::created(function ($detail) {
            optional($detail->kepemilikan->petani)->updateStatusPetani();
        });

        // Setelah detail diupdate
        static::updated(function ($detail) {
            // Cek jika kepemilikan berubah
            if ($detail->isDirty('id_kepemilikan')) {
                // Petani lama
                $oldKepemilikanId = $detail->getOriginal('id_kepemilikan');
                $oldKepemilikan = \App\Models\Kepemilikan::find($oldKepemilikanId);
                optional($oldKepemilikan->petani)->updateStatusPetani();

                // Petani baru
                optional($detail->kepemilikan->petani)->updateStatusPetani();
            }
        });

        static::deleting(function ($detail) {
            // Ambil id petani sebelum relasi hilang
            $idPetani = optional($detail->kepemilikan)->id_petani;

            // Setelah dihapus dari database, panggil update status
            static::deleted(function () use ($idPetani) {
                if ($idPetani) {
                    $petani = \App\Models\Petani::find($idPetani);
                    if ($petani) {
                        $petani->updateStatusPetani();
                    }
                }
            });
        });

        static::updated(function ($detail) {
            $detail->cekStatusPetaniJikaSemuaPerusahaan();
        });

        static::creating(function ($detail) {
            $detail->cekStatusPetaniJikaSemuaPerusahaan();
        });
    }

    public function cekStatusPetaniJikaSemuaPerusahaan()
    {
        $petani = $this->kepemilikan->petani;

        // Hitung jumlah total lahan milik petani
        $total = $petani->detailKepemilikan()->count();

        // Hitung berapa lahan yang status_pengelolaan = perusahaan
        $perusahaan = $petani->detailKepemilikan()
            ->where('status_pengelolaan', 'perusahaan')
            ->count();

        // Jika SEMUA lahan sudah di perusahaan → otomatis berhenti
        if ($total > 0 && $total === $perusahaan) {
            $petani->update(['status' => 'berhenti']);
        }
    }
}