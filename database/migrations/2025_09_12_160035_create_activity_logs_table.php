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
        Schema::create('activity_logs', function (Blueprint $table) {
           $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(); // Hangi firmaya ait
            $table->unsignedBigInteger('user_id')->nullable(); // Hangi kullanıcı
            $table->string('user_name')->nullable(); // Kullanıcı adı (silinirse de görünsün)
            $table->string('user_role')->nullable(); // Kullanıcının rolü
            $table->string('ip_address', 45)->nullable(); // IP adresi
            $table->string('action'); // Yapılan işlem (login, logout, create_service, vb.)
            $table->string('module')->nullable(); // Hangi modül (service, customer, stock, vb.)
            $table->string('description'); // İşlem açıklaması
            $table->json('old_values')->nullable(); // Eski veriler (update işlemlerinde)
            $table->json('new_values')->nullable(); // Yeni veriler (create/update işlemlerinde)
            $table->string('reference_table')->nullable(); // İlgili tablo
            $table->unsignedBigInteger('reference_id')->nullable(); // İlgili kayıt ID
            $table->string('user_agent')->nullable(); // Tarayıcı bilgisi
            $table->timestamps();
            
            // İndeksler
            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
