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
        Schema::create('alamat', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi_kode', 15)->nullable();
            $table->string('provinsi_nama', 100)->nullable();
            $table->string('kabupaten_kode', 15)->nullable();
            $table->string('kabupaten_nama', 100)->nullable();
            $table->string('kecamatan_kode', 15)->nullable();
            $table->string('kecamatan_nama', 100)->nullable();
            $table->string('desa_kode', 15)->nullable();
            $table->string('desa_nama', 100)->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('alamat_detail', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan', 150)->unique();
            $table->timestamps();
        });

        Schema::create('bidang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bidang', 150)->unique();
            $table->timestamps();
        });

        Schema::create('panggol', function (Blueprint $table) {
            $table->id();
            $table->string('pangkat', 100);
            $table->string('golongan', 50);
            $table->timestamps();
        });

        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['administrator', 'pimpinan', 'penyidik'])->default('penyidik');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('bidang')->nullOnDelete();
            $table->foreignId('panggol_id')->nullable()->constrained('panggol')->nullOnDelete();
            $table->foreignId('alamat_id')->nullable()->constrained('alamat')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('nip', 30)->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('foto_profil')->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
            $table->enum('status_pernikahan', ['belum_kawin', 'kawin', 'cerai_hidup', 'cerai_mati'])->nullable();
            $table->string('no_telp', 25)->nullable();
            $table->enum('status', ['pns', 'pppk', 'lainnya'])->default('pns');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user');
        Schema::dropIfExists('panggol');
        Schema::dropIfExists('bidang');
        Schema::dropIfExists('jabatan');
        Schema::dropIfExists('alamat');
    }
};
