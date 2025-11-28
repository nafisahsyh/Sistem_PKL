<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bagi_hasil_petani', function (Blueprint $table) {
            $table->bigIncrements('id_bagi_petani');
            $table->unsignedBigInteger('id_bagi_periode');
            $table->unsignedBigInteger('id_petani');

            $table->decimal('total_luas_ksm', 10, 2);
            $table->decimal('total_nominal', 15, 2);

            // SNAPSHOT DATA PETANI (untuk histori tidak berubah)
            $table->string('nama_petani_snapshot')->nullable();
            $table->string('nik_petani_snapshot')->nullable();
            $table->string('alamat_petani_snapshot')->nullable();
            $table->string('nomor_plasma_snapshot')->nullable();
            $table->string('nomor_koperasi_snapshot')->nullable();

            $table->timestamps();

            $table->foreign('id_bagi_periode')->references('id_bagi_periode')->on('bagi_hasil_periode')->onDelete('cascade');
            $table->foreign('id_petani')->references('id_petani')->on('petani')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bagi_hasil_petani');
    }
};
