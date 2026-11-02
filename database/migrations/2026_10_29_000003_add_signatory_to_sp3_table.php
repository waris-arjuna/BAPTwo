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
        Schema::table('sp3', function (Blueprint $table) {
            $table->foreignId('penyidik_utama_id')
                ->nullable()
                ->after('dokumen_id')
                ->constrained('user')
                ->nullOnDelete();
            $table->string('penandatangan_nama', 150)
                ->nullable()
                ->after('penyidik_utama_id');
            $table->string('penandatangan_jabatan', 150)
                ->nullable()
                ->after('penandatangan_nama');
        });

        Schema::create('sp3_ppns_anggota', function (Blueprint $table) {
            $table->foreignId('sp3_id')->constrained('sp3')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
            $table->primary(['sp3_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp3_ppns_anggota');

        Schema::table('sp3', function (Blueprint $table) {
            $table->dropForeign(['penyidik_utama_id']);
            $table->dropColumn(['penyidik_utama_id', 'penandatangan_nama', 'penandatangan_jabatan']);
        });
    }
};
