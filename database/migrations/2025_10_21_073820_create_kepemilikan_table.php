<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kepemilikan', function (Blueprint $table) {
            $table->bigIncrements('id_kepemilikan');
            // Foreign keys
            $table->unsignedBigInteger('id_petani'); // FK ke tabel petani
            $table->unsignedBigInteger('id_lahan');  // FK ke tabel lahan

            // Data legalitas
            $table->string('nomor_SHM', 100)->nullable();       // Nomor legalitas lahan SHM
            $table->string('nomor_sporadik', 100)->nullable();  // Nomor legalitas lahan sporadik

            // Data luas & pajak
            $table->decimal('luas_surat', 10, 2)->nullable();   // Luas lahan berdasarkan surat
            $table->string('nomor_pbb', 100)->nullable();       // Nomor PBB
            $table->decimal('jumlah_pbb', 10, 2)->nullable();   // Jumlah pajak PBB

            // Status & waktu kepemilikan
            $table->enum('status_kepemilikan', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            // Foreign key constraints
            $table->foreign('id_petani')->references('id_petani')->on('petani')->onDelete('cascade');
            $table->foreign('id_lahan')->references('id_lahan')->on('lahan')->onDelete('cascade');
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepemilikan');
    }
};
