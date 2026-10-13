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
        Schema::create('dpo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_perkara_id')->constrained('berkas_perkara')->cascadeOnDelete();
            $table->string('nomor_surat', 150)->nullable();
            $table->date('tanggal_penetapan')->nullable();
            $table->date('tanggal_pencabutan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->text('keterangan')->nullable();
            $table->text('alasan_pencabutan')->nullable();
            $table->foreignId('dokumen_id')->nullable()->constrained('dokumen')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpo');
    }
};
