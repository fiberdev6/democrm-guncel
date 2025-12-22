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
        // ENUM sütununu güncellemek için ALTER TABLE kullanıyoruz
        DB::statement("ALTER TABLE `offers` MODIFY COLUMN `durum` ENUM('0', '1', '2', '3') NOT NULL DEFAULT '0'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri almak isterseniz eski haline döndür
        DB::statement("ALTER TABLE `offers` MODIFY COLUMN `durum` ENUM('0', '1') NOT NULL DEFAULT '1'");
    }
};
