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
        Schema::create('lahan', function (Blueprint $table) {
            $table->bigIncrements('id_lahan');
            // Foreign keys
            $table->unsignedBigInteger('id_desa');
            $table->unsignedBigInteger('id_tahun_tanam');

            // Data
            $table->decimal('luas_peta', 10, 2);

            // Relasi antar tabel
            $table->foreign('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('id_tahun_tanam')->references('id_tahun_tanam')->on('tahun_tanam')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lahan');
    }
};
