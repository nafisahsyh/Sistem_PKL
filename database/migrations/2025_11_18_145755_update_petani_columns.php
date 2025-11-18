<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petani', function (Blueprint $table) {

            // 1. Hapus UNIQUE nomor_anggota_koperasi
            if (Schema::hasColumn('petani', 'nomor_anggota_koperasi')) {
                $table->dropUnique('petani_nomor_anggota_koperasi_unique');
            }

            // 2. nomor_anggota_koperasi -> NULLABLE
            $table->string('nomor_anggota_koperasi', 100)->nullable()->change();

            // 3. nomor_anggota_plasma -> NULLABLE + UNIQUE
            $table->string('nomor_anggota_plasma', 100)->nullable()->change();

            // Tambahkan UNIQUE untuk nomor_anggota_plasma (jika belum ada)
            $table->unique('nomor_anggota_plasma', 'petani_nomor_anggota_plasma_unique');
        });
    }

    public function down(): void
    {
        
    }
};
