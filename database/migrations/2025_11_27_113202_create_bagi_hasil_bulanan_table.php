<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bagi_hasil_bulanan', function (Blueprint $table) {
            $table->bigIncrements('id_bagi_bulanan');
            $table->unsignedBigInteger('id_desa');
            $table->unsignedBigInteger('id_tahun_tanam');
            $table->decimal('luasan_total_snapshot', 10, 2)->nullable();
            $table->decimal('sisa_saldo_snapshot', 15, 2)->nullable();
            $table->unsignedTinyInteger('bulan'); // 1–12
            $table->year('tahun');
            $table->date('tanggal_bagi');
            $table->decimal('total_bagian', 15, 2);
            $table->timestamps();

            // Foreign Key
            $table->foreign('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('id_tahun_tanam')->references('id_tahun_tanam')->on('tahun_tanam')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bagi_hasil_bulanan');
    }
};
