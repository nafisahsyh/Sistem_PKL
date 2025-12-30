<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saldo', function (Blueprint $table) {
            $table->bigIncrements('id_saldo');
            $table->unsignedBigInteger('id_petani');
            $table->unsignedBigInteger('id_desa');
            $table->unsignedBigInteger('id_tahun_tanam');
            $table->string('bulan_awal', 7)->nullable();
            $table->string('bulan_akhir', 7)->nullable();

            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('saldo_awal', 15, 2)->default(0);

            $table->timestamps();

            $table->foreign('id_petani')->references('id_petani')->on('petani')->onDelete('cascade');
            $table->foreign('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('id_tahun_tanam')->references('id_tahun_tanam')->on('tahun_tanam')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo');
    }
};
