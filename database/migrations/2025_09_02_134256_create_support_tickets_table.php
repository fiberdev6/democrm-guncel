<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('category');
            $table->string('subject');
            $table->enum('priority', ['yuksek', 'orta'])->default('orta');
            $table->text('description');
            $table->json('attachments')->nullable(); // Dosya yolları JSON formatında
            $table->enum('status', ['acik', 'cevaplandi', 'kapali'])->default('acik');
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('tb_user')->onDelete('cascade');
            
            $table->index(['tenant_id', 'status']);
            $table->index('ticket_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_tickets');
    }
};