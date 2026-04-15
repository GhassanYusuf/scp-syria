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
            $table->string('vehicle_plate')->nullable()->after('phone');
            $table->enum('source', ['walk_in', 'reservation'])->default('reservation')->after('vehicle_plate');
            $table->decimal('total_fee', 10, 2)->nullable()->after('status');
            $table->enum('payment_method', ['cash', 'upload'])->nullable()->after('total_fee');
            $table->string('payment_proof')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_proof');
            $table->string('customer_name')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['vehicle_plate', 'source', 'total_fee', 'payment_method', 'payment_proof', 'paid_at']);
        });
    }
};
