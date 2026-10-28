<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user')
            ->where('role', 'pimpinan')
            ->update(['role' => 'umum']);

        Schema::table('user', function (Blueprint $table) {
            $table->enum('role', ['administrator', 'penyidik', 'umum'])
                ->default('penyidik')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->enum('role', ['administrator', 'pimpinan', 'penyidik', 'umum'])
                ->default('penyidik')
                ->change();
        });
    }
};
