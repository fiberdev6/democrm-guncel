<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $plans = DB::table('subscription_plans')->get();

        foreach ($plans as $plan) {
            if (!empty($plan->limits) && str_starts_with(trim($plan->limits), '[')) {
                // PHP array formatını JSON formatına çevir
                $fixed = str_replace(['[', ']'], ['{', '}'], $plan->limits);

                // Tek tırnakları çift tırnağa çevir
                $fixed = str_replace("'", '"', $fixed);

                // Güncelle
                DB::table('subscription_plans')
                    ->where('id', $plan->id)
                    ->update([
                        'limits' => $fixed
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            //
        });
    }
};
