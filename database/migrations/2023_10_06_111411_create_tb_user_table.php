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
        Schema::create('tb_user', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('tel')->nullable();
            $table->string('il')->nullable();
            $table->string('ilce')->nullable();
            $table->string('address')->nullable();
            $table->string('eposta')->nullable();
            $table->date('baslamaTarihi')->nullable();
            $table->date('ayrilmaTarihi')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_user');
    }
};
