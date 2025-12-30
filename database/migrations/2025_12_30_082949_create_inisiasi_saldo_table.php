<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inisiasi_saldo', function (Blueprint $table) {
            $table->bigIncrements('id_inisiasi_saldo');

            // Relasi logis (opsional untuk tracking)
            $table->unsignedBigInteger('id_petani')->nullable();
            $table->unsignedBigInteger('id_desa')->nullable();
            $table->unsignedBigInteger('id_tahun_tanam')->nullable();

            // SNAPSHOT STATIS (DIPAKAI DI INDEX)
            $table->string('nomor_plasma');
            $table->string('nomor_koperasi')->nullable();
            $table->string('nama_petani');
            $table->string('desa');
            $table->string('tahun_tanam');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inisiasi_saldo');
    }
};
