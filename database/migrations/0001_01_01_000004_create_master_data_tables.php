<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['jabatan', 'bidang', 'panggol'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (! Schema::hasTable('pasal')) {
            Schema::create('pasal', function (Blueprint $table) {
                $table->id();
                $table->string('nama_pasal', 150);
                $table->string('pasal', 150);
                $table->string('nomor', 50)->nullable();
                $table->string('tahun', 10)->nullable();
                $table->text('isi')->nullable();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('instansi_hukum')) {
            Schema::create('instansi_hukum', function (Blueprint $table) {
                $table->id();
                $table->enum('jenis', ['kejaksaan', 'pengadilan']);
                $table->string('nama', 150);
                $table->string('wilayah', 100)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('jaksa_penuntut')) {
            Schema::create('jaksa_penuntut', function (Blueprint $table) {
                $table->id();
                $table->foreignId('instansi_hukum_id')->constrained('instansi_hukum')->cascadeOnDelete();
                $table->string('nama_jaksa', 225);
                $table->string('nip', 30)->nullable();
                $table->string('pangkat', 100)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jaksa_penuntut');
        Schema::dropIfExists('instansi_hukum');
        Schema::dropIfExists('pasal');

        foreach (['jabatan', 'bidang', 'panggol'] as $tableName) {
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
