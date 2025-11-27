<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->bigIncrements('id_transaksi');
            $table->unsignedBigInteger('id_petani');

            $table->enum('tipe', ['credit_bagihasil', 'credit_mandiri', 'debit_pengambilan']);
            $table->enum('metode', ['cash', 'transfer'])->nullable(); // hanya untuk debit
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal');
            $table->string('keterangan', 255)->nullable();

            $table->timestamps();

            $table->foreign('id_petani')->references('id_petani')->on('petani')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
