<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingLot;
use App\Models\CarRegistry;

class ParkingLotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lots = [
            [
                'name' => 'موقف البرامكة',
                'address' => 'شارع البرامكة، دمشق',
                'total_capacity' => 120,
                'price_per_hour' => 1500,
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'working_hours' => '06:00 - 22:00',
            ],
            [
                'name' => 'موقف المرجة',
                'address' => 'ساحة المرجة، دمشق القديمة',
                'total_capacity' => 80,
                'price_per_hour' => 2000,
                'working_hours' => '24/7',
                'latitude' => 33.5105,
                'longitude' => 36.3118,
            ],
            [
                'name' => 'موقف المالكي',
                'address' => 'شارع المالكي، دمشق',
                'total_capacity' => 60,
                'price_per_hour' => 1200,
                'working_hours' => '24/7',
                'latitude' => 33.5230,
                'longitude' => 36.3050,
            ],
            [
                'name' => 'موقف المزة',
                'address' => 'شارع المزة الأوتوستراد',
                'total_capacity' => 100,
                'price_per_hour' => 1800,
                'working_hours' => '05:00 - 23:00',
                'latitude' => 33.5080,
                'longitude' => 36.2650,
            ],
            [
                'name' => 'موقف كفرسوسة',
                'address' => 'طريق المزة، كفرسوسة',
                'total_capacity' => 75,
                'price_per_hour' => 1000,
                'working_hours' => '24/7',
                'latitude' => 33.4980,
                'longitude' => 36.2450,
            ],
        ];

        foreach ($lots as $lotData) {
            $lot = ParkingLot::create($lotData);

            // Seed some active car registries (walk-ins + bookings) for realistic availability
            $occupied = rand(10, (int)($lotData['total_capacity'] * 0.4)); // 10-40% occupied
            for ($i = 0; $i < $occupied; $i++) {
                CarRegistry::create([
                    'parking_lot_id' => $lot->id,
                    'plate_number' => sprintf('%d س دم %s', rand(100, 999), substr(md5(rand()), 0, 3)),
                    'entry_time' => now()->subHours(rand(1, 12)),
                    'exit_time' => rand(0, 4) > 3 ? null : now()->addHours(rand(1, 6)), // Most still active
                ]);
            }
        }
    }
}
?>

