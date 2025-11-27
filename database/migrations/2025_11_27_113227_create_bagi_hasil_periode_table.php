<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bagi_hasil_periode', function (Blueprint $table) {
            $table->bigIncrements('id_bagi_periode');
            $table->unsignedBigInteger('id_desa');
            $table->unsignedBigInteger('id_tahun_tanam');

            $table->unsignedTinyInteger('bulan_awal');  // ex: 1
            $table->unsignedTinyInteger('bulan_akhir'); // ex: 2
            $table->year('tahun');

            $table->date('tanggal_bagi');
            $table->decimal('total_periode', 15, 2);
            $table->timestamps();

            $table->foreign('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('id_tahun_tanam')->references('id_tahun_tanam')->on('tahun_tanam')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bagi_hasil_periode');
    }
};
