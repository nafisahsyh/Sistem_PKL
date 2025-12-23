<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saldo_lalu', function (Blueprint $table) {
            $table->bigIncrements('id_saldo_lalu');
            $table->unsignedBigInteger('id_bagi_bulanan');

            // Relasi petani & wilayah
            $table->unsignedBigInteger('id_petani');
            $table->unsignedBigInteger('id_desa');
            $table->unsignedBigInteger('id_tahun_tanam');

            // Saldo petani saat awal periode
            $table->decimal('saldo_lalu', 15, 2)->default(0);

            $table->timestamp('created_at')->useCurrent();

            // Periode bulanan
            $table->foreign('id_bagi_bulanan')
                ->references('id_bagi_bulanan')
                ->on('bagi_hasil_bulanan')
                ->cascadeOnDelete();

            // Petani
            $table->foreign('id_petani')
                ->references('id_petani')
                ->on('petani')
                ->cascadeOnDelete();

            // Desa
            $table->foreign('id_desa')
                ->references('id_desa')
                ->on('desa')
                ->restrictOnDelete();

            // Tahun tanam
            $table->foreign('id_tahun_tanam')
                ->references('id_tahun_tanam')
                ->on('tahun_tanam')
                ->restrictOnDelete();

            // Index tambahan (opsional tapi disarankan)
            $table->index(['id_bagi_bulanan', 'id_petani']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_lalu');
    }
};
