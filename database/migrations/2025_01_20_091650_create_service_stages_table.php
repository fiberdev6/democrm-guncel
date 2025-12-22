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
        Schema::create('service_stages', function (Blueprint $table) {
            $table->id();
            $table->string('asama')->nullable();
            $table->text('altAsamalar')->nullable();
            $table->integer('sira')->nullable();
            $table->boolean('ilkServis')->default(0)->nullable();
            $table->boolean('sonServis')->default(0)->nullable();
            $table->bigInteger('firma_id')->nullable();
            $table->string('asama_renk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_stages');
    }
};
