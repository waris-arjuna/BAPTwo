<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (! Schema::hasColumn('user', 'foto_profil')) {
                $table->string('foto_profil')->nullable()->after('password');
            }
            if (! Schema::hasColumn('user', 'tempat_lahir')) {
                $table->string('tempat_lahir', 100)->nullable()->after('foto_profil');
            }
            if (! Schema::hasColumn('user', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (! Schema::hasColumn('user', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable()->after('tanggal_lahir');
            }
            if (! Schema::hasColumn('user', 'status_pernikahan')) {
                $table->enum('status_pernikahan', ['belum_kawin', 'kawin', 'cerai_hidup', 'cerai_mati'])->nullable()->after('jenis_kelamin');
            }
            if (! Schema::hasColumn('user', 'no_telp')) {
                $table->string('no_telp', 25)->nullable()->after('status_pernikahan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $columns = array_filter(
                ['foto_profil', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_pernikahan', 'no_telp'],
                fn (string $col): bool => Schema::hasColumn('user', $col)
            );

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
