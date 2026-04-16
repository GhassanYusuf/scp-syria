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
        Schema::table('bookings', function (Blueprint $table) {
            // Stores {base: float, rules: {1:float, ..., 7:float}} at booking creation.
            // Checkout fee calculation uses this snapshot so price changes never affect
            // in-progress or historical bookings.
            $table->json('pricing_snapshot')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('pricing_snapshot');
        });
    }
};
