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
            $table->string('nomor_surat_penangkapan', 150)->nullable()->after('alasan_pencabutan');
            $table->date('tanggal_surat_penangkapan')->nullable()->after('nomor_surat_penangkapan');
            $table->date('tanggal_penangkapan')->nullable()->after('tanggal_surat_penangkapan');
            $table->foreignId('dokumen_penangkapan_id')->nullable()->after('tanggal_penangkapan')->constrained('dokumen')->nullOnDelete();
            $table->text('keterangan_penangkapan')->nullable()->after('dokumen_penangkapan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dpo', function (Blueprint $table) {
            $table->dropForeign(['dokumen_penangkapan_id']);
            $table->dropColumn([
                'nomor_surat_penangkapan',
                'tanggal_surat_penangkapan',
                'tanggal_penangkapan',
                'dokumen_penangkapan_id',
                'keterangan_penangkapan',
            ]);
        });
    }
};
