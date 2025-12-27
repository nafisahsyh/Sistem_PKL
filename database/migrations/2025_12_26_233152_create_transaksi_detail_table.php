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
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->bigIncrements('id_transaksi_detail');
            $table->unsignedBigInteger('id_transaksi');

            $table->string('periode_awal');   // contoh: 2025-01
            $table->string('periode_akhir');  // contoh: 2025-02

            $table->decimal('nominal', 15, 2); // nominal periode ini

            $table->timestamps();

            $table->foreign('id_transaksi')
                ->references('id_transaksi')
                ->on('transaksi')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};
