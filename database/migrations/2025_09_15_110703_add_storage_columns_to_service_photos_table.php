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
        Schema::table('service_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('service_photos', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('resimyol');
            }
            
            // Orjinal dosya adı
            if (!Schema::hasColumn('service_photos', 'original_name')) {
                $table->string('original_name')->nullable()->after('file_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_photos', function (Blueprint $table) {
            //
        });
    }
};
