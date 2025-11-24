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
        Schema::create('detail_kepemilikan', function (Blueprint $table) {
            $table->bigIncrements('id_detail_kepemilikan');
            $table->unsignedBigInteger('id_kepemilikan'); // FK ke kepemilikan
            $table->unsignedBigInteger('id_lahan');       // FK ke lahan

            // Data legalitas & pajak tiap lahan
            $table->string('kode_lahan',15)->nullable();
            $table->string('nomor_SHM', 100)->nullable();
            $table->string('nama_SHM',100)->nullable();
            $table->string('nomor_sporadik', 100)->nullable();
            $table->string('nama_sporadik',100)->nullable();
            $table->string('nomor_kavling', 100)->nullable();
            $table->decimal('luas_surat', 10, 2)->nullable();
            $table->string('nomor_pbb', 100)->nullable();
            $table->decimal('jumlah_pbb', 10, 2)->nullable();
            $table->enum('posisi_surat', ['Notaris', 'PTP', 'Koperasi', 'Petani'])->nullable();
            $table->string('status_penyerahan')->nullable();
            $table->string('pdf_scan_shm', 255)->nullable();
            $table->string('pdf_scan_peta', 255)->nullable();
            $table->enum('status_kepemilikan', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status_pengelolaan', ['Mandiri', 'KSM', 'Perusahaan'])->default('KSM');
            $table->timestamps();

            // Relasi foreign key
            $table->foreign('id_kepemilikan')
                ->references('id_kepemilikan')
                ->on('kepemilikan')
                ->onDelete('cascade');

            $table->foreign('id_lahan')
                ->references('id_lahan')
                ->on('lahan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_kepemilikan');
    }
};
