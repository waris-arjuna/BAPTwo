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
        Schema::table('dpo', function (Blueprint $table) {
            $table->string('nomor_surat_penahanan', 150)->nullable()->after('tanggal_penangkapan');
            $table->date('tanggal_penahanan')->nullable()->after('nomor_surat_penahanan');
        });

        Schema::table('dokumen', function (Blueprint $table) {
            $table->foreignId('dpo_id')
                ->nullable()
                ->after('berkas_perkara_id')
                ->constrained('dpo')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            $table->dropForeign(['dpo_id']);
            $table->dropColumn('dpo_id');
        });

        Schema::table('dpo', function (Blueprint $table) {
            $table->dropColumn(['nomor_surat_penahanan', 'tanggal_penahanan']);
        });
    }
};
