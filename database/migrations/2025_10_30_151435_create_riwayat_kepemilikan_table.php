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
        Schema::create('riwayat_kepemilikan', function (Blueprint $table) {
            $table->id('id_riwayat');

            $table->unsignedBigInteger('id_lahan');
            $table->unsignedBigInteger('id_petani_sebelum');
            $table->unsignedBigInteger('id_petani_sesudah')->nullable();

            $table->date('tanggal_ganti')->default(DB::raw('CURRENT_DATE'));
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_lahan')
                ->references('id_lahan')->on('lahan')
                ->onDelete('cascade');

            $table->foreign('id_petani_sebelum')
                ->references('id_petani')->on('petani')
                ->onDelete('cascade');

            $table->foreign('id_petani_sesudah')
                ->references('id_petani')->on('petani')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kepemilikan');
    }
};
