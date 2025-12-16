<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->bigIncrements('id_transaksi');
            $table->unsignedBigInteger('id_bagi_bulanan')->nullable();
            $table->unsignedBigInteger('id_petani');
            $table->unsignedBigInteger('id_desa');
            $table->unsignedBigInteger('id_tahun_tanam');

            $table->enum('tipe', ['credit_bagihasil', 'credit_mandiri', 'debit_pengambilan']);
            $table->enum('metode', ['cash', 'transfer'])->nullable(); // hanya untuk debit
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal');
            $table->string('keterangan', 255)->nullable();
            $table->integer('no_urut')->nullable();
            $table->string('no_bukti')->nullable();
            $table->string('bulan_awal', 7)->nullable();
            $table->string('bulan_akhir', 7)->nullable();


            $table->timestamps();

            $table->foreign('id_bagi_bulanan')->references('id_bagi_bulanan')->on('bagi_hasil_bulanan')->onDelete('cascade');
            $table->foreign('id_petani')->references('id_petani')->on('petani')->onDelete('cascade');
            $table->foreign('id_desa')->references('id_desa')->on('desa')->onDelete('cascade');
            $table->foreign('id_tahun_tanam')->references('id_tahun_tanam')->on('tahun_tanam')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
