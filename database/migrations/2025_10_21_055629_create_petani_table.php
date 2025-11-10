<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petani', function (Blueprint $table) {
            $table->bigIncrements('id_petani');
            $table->string('nomor_anggota_plasma', 100)->unique();
            $table->string('nomor_anggota_koperasi', 100)->unique()->nullable();
            $table->string('NIK', 16)->unique()->nullable();
            $table->string('nama', 255);
            $table->string('alamat', 255);
            $table->enum('status', ['aktif', 'tidak_aktif', 'berhenti'])->default('aktif');
            $table->string('pdf_scan_ktp', 255)->nullable();
            $table->string('pdf_scan_kk', 255)->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petani');
    }
};
