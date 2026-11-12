<?php

declare(strict_types=1);

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
        Schema::dropIfExists('jaksa_penuntut');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('jaksa_penuntut')) {
            Schema::create('jaksa_penuntut', function (Blueprint $table) {
                $table->id();
                $table->foreignId('instansi_hukum_id')->constrained('instansi_hukum')->cascadeOnDelete();
                $table->string('nama_jaksa');
                $table->string('nip', 30)->nullable();
                $table->string('pangkat', 50)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('user')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('user')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
};
