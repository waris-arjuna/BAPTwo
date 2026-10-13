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
        // 1. Tabel perkara (Informasi Pra-SPDP Lean - ref: D-008)
        Schema::create('perkara', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_laporan_kejadian', 100)->unique();
            $table->date('tanggal_laporan_kejadian');
            $table->string('nomor_surat_perintah', 100)->nullable()->unique();
            $table->date('tanggal_surat_perintah')->nullable();
            $table->unsignedBigInteger('tkp_id')->nullable()->index();
            $table->text('deskripsi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tabel dokumen (Sentralisasi File Wajib PDF - ref: D-007, D-008)
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perkara_id')->nullable()->constrained('perkara')->cascadeOnDelete();
            $table->unsignedBigInteger('berkas_perkara_id')->nullable()->index();
            $table->unsignedBigInteger('tahapan_perkara_id')->nullable()->index();
            $table->enum('kategori', ['lapkej', 'sprindik', 'spdp', 'tahapan', 'dpo', 'sp3', 'lainnya'])->index();
            $table->string('nama_dokumen', 150);
            $table->string('nama_file_asli', 255);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Tabel audit_log (Case History & Scheduler-Ready - ref: D-008)
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('perkara_id')->nullable()->constrained('perkara')->cascadeOnDelete();
            $table->unsignedBigInteger('berkas_perkara_id')->nullable()->index();
            $table->string('aksi', 100);
            $table->string('tabel', 100)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('dokumen');
        Schema::dropIfExists('perkara');
    }
};
