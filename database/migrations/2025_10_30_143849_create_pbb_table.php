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
        Schema::create('pbb', function (Blueprint $table) {
            $table->bigIncrements('id_pbb');
            $table->unsignedBigInteger('id_detail_kepemilikan');
            $table->year('tahun');
            $table->decimal('jumlah', 12, 2);
            $table->enum('status', ['lunas', 'belum'])->default('belum');
            $table->timestamps();

            $table->foreign('id_detail_kepemilikan')
                ->references('id_detail_kepemilikan')
                ->on('detail_kepemilikan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbb');
    }
};
