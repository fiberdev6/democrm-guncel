<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up()
    {
        Schema::table('super_admin_invoices', function (Blueprint $table) {
            $table->json('payment_details')->nullable()->after('faturaPdf');
        });
    }

    public function down()
    {
        Schema::table('super_admin_invoices', function (Blueprint $table) {
            $table->dropColumn('payment_details');
        });
    }
};
