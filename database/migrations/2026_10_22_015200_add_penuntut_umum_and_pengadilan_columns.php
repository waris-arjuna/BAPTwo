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
        // 1. Tambah field Penuntut Umum pada tabel perkara (ref: D-053, Poin 2 & Poin 8)
        Schema::table('perkara', function (Blueprint $table) {
            if (! Schema::hasColumn('perkara', 'penuntut_umum_instansi_id')) {
                $table->foreignId('penuntut_umum_instansi_id')
                    ->nullable()
                    ->after('tkp_id')
                    ->constrained('instansi_hukum')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('perkara', 'penuntut_umum_nama')) {
                $table->string('penuntut_umum_nama', 255)->nullable()->after('penuntut_umum_instansi_id');
            }
            if (! Schema::hasColumn('perkara', 'penuntut_umum_nip')) {
                $table->string('penuntut_umum_nip', 50)->nullable()->after('penuntut_umum_nama');
            }
            if (! Schema::hasColumn('perkara', 'penuntut_umum_pangkat')) {
                $table->string('penuntut_umum_pangkat', 100)->nullable()->after('penuntut_umum_nip');
            }
        });

        // 2. Tambah field Pengadilan & Amar Putusan pada tabel tahapan_perkara (ref: D-053, Poin 8)
        Schema::table('tahapan_perkara', function (Blueprint $table) {
            if (! Schema::hasColumn('tahapan_perkara', 'pengadilan_instansi_id')) {
                $table->foreignId('pengadilan_instansi_id')
                    ->nullable()
                    ->after('catatan')
                    ->constrained('instansi_hukum')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('tahapan_perkara', 'amar_putusan')) {
                $table->text('amar_putusan')->nullable()->after('pengadilan_instansi_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahapan_perkara', function (Blueprint $table) {
            if (Schema::hasColumn('tahapan_perkara', 'pengadilan_instansi_id')) {
                $table->dropConstrainedForeignId('pengadilan_instansi_id');
            }
            if (Schema::hasColumn('tahapan_perkara', 'amar_putusan')) {
                $table->dropColumn('amar_putusan');
            }
        });

        Schema::table('perkara', function (Blueprint $table) {
            if (Schema::hasColumn('perkara', 'penuntut_umum_instansi_id')) {
                $table->dropConstrainedForeignId('penuntut_umum_instansi_id');
            }
            $dropCols = [];
            foreach (['penuntut_umum_nama', 'penuntut_umum_nip', 'penuntut_umum_pangkat'] as $col) {
                if (Schema::hasColumn('perkara', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (! empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
