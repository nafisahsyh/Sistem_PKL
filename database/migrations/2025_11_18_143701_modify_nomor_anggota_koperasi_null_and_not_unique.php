<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            // Hapus unique index nomor_anggota_plasma
            $table->dropUnique('petani_nomor_anggota_plasma_unique');

            // Set nullable
            $table->string('nomor_anggota_plasma', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tidak perlu mengembalikan unique
    }
};
