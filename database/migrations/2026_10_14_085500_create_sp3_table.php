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
        Schema::create('sp3', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_perkara_id')->constrained('berkas_perkara')->cascadeOnDelete();
            $table->string('nomor_surat', 150)->index();
            $table->date('tanggal_sp3')->index();
            $table->text('keterangan');
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
        Schema::dropIfExists('sp3');
    }
};
