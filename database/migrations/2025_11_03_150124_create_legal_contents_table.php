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
     Schema::create('legal_contents', function (Blueprint $table) {
        $table->id();
        $table->enum('type', ['terms', 'privacy'])->unique();
        $table->text('content')->nullable();
        $table->timestamps();
    });
    
    // Varsayılan kayıtları ekle
    DB::table('legal_contents')->insert([
        ['type' => 'terms', 'content' => '', 'created_at' => now(), 'updated_at' => now()],
        ['type' => 'privacy', 'content' => '', 'created_at' => now(), 'updated_at' => now()]
    ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_contents');
    }
};
