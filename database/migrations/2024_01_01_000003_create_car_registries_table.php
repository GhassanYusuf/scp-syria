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
        Schema::create('car_registries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_lot_id')->constrained()->onDelete('cascade');
$table->string('plate_number');
            $table->dateTime('entry_time');
            $table->dateTime('exit_time')->nullable();
            $table->timestamps();

            $table->index(['parking_lot_id', 'exit_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_registries');
    }
};
?>

