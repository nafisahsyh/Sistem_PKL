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
            $table->unsignedBigInteger('id_petani'); // siapa pemiliknya

            // Status & waktu kepemilikan

            $table->timestamps();

            // Relasi ke tabel petani
            $table->foreign('id_petani')->references('id_petani')
                ->on('petani')
                ->onDelete('cascade');
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
