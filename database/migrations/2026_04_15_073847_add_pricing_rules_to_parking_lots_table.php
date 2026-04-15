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
        Schema::table('parking_lots', function (Blueprint $table) {
            // JSON: {"1":100,"2":100,...,"7":150}  keys = ISO weekday (1=Mon,7=Sun), values = price/hour
            $table->json('pricing_rules')->nullable()->after('price_per_hour');
        });
    }

    public function down(): void
    {
        Schema::table('parking_lots', function (Blueprint $table) {
            $table->dropColumn('pricing_rules');
        });
    }
};
