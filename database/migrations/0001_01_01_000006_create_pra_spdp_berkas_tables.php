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
        // 1. Tabel tkp (Informasi Tempat Kejadian Perkara - ref: F-07, D-008, D-009)
        Schema::create('tkp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alamat_id')->nullable()->constrained('alamat')->nullOnDelete();
            $table->enum('klasifikasi', ['retailer', 'distributor'])->nullable();
            $table->enum('legalitas', ['legal', 'ilegal'])->nullable();
            $table->enum('modus', ['online', 'offline'])->nullable();
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tambahkan kolom penguncian tahap pembentukan perkara pra-SPDP pada tabel perkara
        Schema::table('perkara', function (Blueprint $table) {
            $table->enum('tahap_perkara_status', ['draft', 'final'])->nullable()->after('sprindik_finalized_by')->index();
            $table->timestamp('perkara_locked_at')->nullable()->after('tahap_perkara_status');
            $table->foreignId('perkara_finalized_by')->nullable()->after('perkara_locked_at')->constrained('user')->nullOnDelete();
        });

        // 2. Tabel perkara_ppns (PPNS Utama dan PPNS Anggota - ref: F-08, D-008)
        Schema::create('perkara_ppns', function (Blueprint $table) {
            $table->foreignId('perkara_id')->constrained('perkara')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
            $table->enum('peran', ['utama', 'anggota'])->default('anggota');
            $table->primary(['perkara_id', 'user_id']);
        });

        // 3. Tabel tersangka (Data Tersangka - ref: F-03, D-008)
        Schema::create('tersangka', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perkara_id')->constrained('perkara')->cascadeOnDelete();
            $table->string('nama', 150)->nullable();
            $table->string('alias', 150)->nullable();
            $table->string('bin_binti', 150)->nullable();
            $table->string('nik', 20)->nullable()->index();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
            $table->enum('agama', ['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu', 'lainnya'])->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->string('kewarganegaraan', 50)->nullable()->default('WNI');
            $table->foreignId('alamat_id')->nullable()->constrained('alamat')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Tabel berkas_perkara (1 Tersangka = 1 Berkas Perkara, Nomor Berkas Manual - ref: F-02, F-33, D-008)
        Schema::create('berkas_perkara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perkara_id')->constrained('perkara')->cascadeOnDelete();
            $table->foreignId('tersangka_id')->unique()->constrained('tersangka')->cascadeOnDelete();
            $table->string('nomor_berkas', 150)->nullable()->index();
            $table->date('tanggal_spdp')->nullable()->index();
            $table->enum('status_formal', ['selesai', 'tidak_selesai'])->nullable()->default(null)->index();
            $table->string('status_proses', 50)->default('Baru')->index();
            $table->unsignedTinyInteger('progress_persen')->default(0);
            $table->boolean('is_dpo')->default(false)->index();
            $table->date('sla_deadline')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Tabel berkas_pasal (Pasal Berlapis per Berkas Perkara - ref: F-04, D-008)
        Schema::create('berkas_pasal', function (Blueprint $table) {
            $table->foreignId('berkas_perkara_id')->constrained('berkas_perkara')->cascadeOnDelete();
            $table->foreignId('pasal_id')->constrained('pasal')->cascadeOnDelete();
            $table->primary(['berkas_perkara_id', 'pasal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas_pasal');
        Schema::dropIfExists('berkas_perkara');
        Schema::dropIfExists('tersangka');
        Schema::dropIfExists('perkara_ppns');

        Schema::table('perkara', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perkara_finalized_by');
            $table->dropColumn(['tahap_perkara_status', 'perkara_locked_at']);
        });

        Schema::dropIfExists('tkp');
    }
};
