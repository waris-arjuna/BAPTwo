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
        Schema::create('tahapan_perkara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_perkara_id')->constrained('berkas_perkara')->cascadeOnDelete();
            $table->string('jenis_tahap', 50)->index();
            $table->string('nomor_surat', 150)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft')->index();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['berkas_perkara_id', 'jenis_tahap']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahapan_perkara');
    }
};
