<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bagi_hasil_petani', function (Blueprint $table) {
            $table->unsignedBigInteger('id_bagi_bulanan')->nullable()->after('id_petani');

            // Tambahkan foreign key ke tabel bagi_hasil_bulanan
            $table->foreign('id_bagi_bulanan')
                ->references('id_bagi_bulanan')
                ->on('bagi_hasil_bulanan')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('bagi_hasil_petani', function (Blueprint $table) {
            $table->dropForeign(['id_bagi_bulanan']);
            $table->dropColumn('id_bagi_bulanan');
        });
    }
};
