<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ParkingLot;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $lots = ParkingLot::all();

        if ($lots->isEmpty()) {
            return;
        }

        $names = ['أحمد الخطيب', 'سامر العلي', 'رنا موسى', 'خالد إبراهيم', 'منى حسن',
                  'عمر الزعبي', 'لينا صالح', 'فراس نعمة', 'هبة الدار', 'باسل قاسم',
                  'نور السيد', 'وسيم طه', 'ديانا فارس', 'زياد جبر', 'ريم عبدالله'];

        $phones = ['0912345678', '0923456789', '0934567890', '0945678901', '0956789012',
                   '0967890123', '0978901234', '0989012345', '0990123456', '0901234567'];

        $now = Carbon::now();

        // Seed bookings spread across the last 7 days
        foreach ($lots as $lot) {
            // 2–5 active bookings per lot
            $activeCount = rand(2, 5);
            for ($i = 0; $i < $activeCount; $i++) {
                $start = $now->clone()->subHours(rand(1, 5));
                $end   = $start->clone()->addHours(rand(1, 4));
                Booking::create([
                    'parking_lot_id' => $lot->id,
                    'customer_name'  => $names[array_rand($names)],
                    'phone'          => $phones[array_rand($phones)],
                    'start_time'     => $start,
                    'end_time'       => $end,
                    'status'         => 'active',
                    'created_at'     => $start,
                    'updated_at'     => $start,
                ]);
            }

            // 3–8 completed bookings spread over the past 7 days
            $completedCount = rand(3, 8);
            for ($i = 0; $i < $completedCount; $i++) {
                $daysAgo = rand(0, 6);
                $start   = $now->clone()->subDays($daysAgo)->subHours(rand(2, 10));
                $end     = $start->clone()->addHours(rand(1, 3));
                Booking::create([
                    'parking_lot_id' => $lot->id,
                    'customer_name'  => $names[array_rand($names)],
                    'phone'          => $phones[array_rand($phones)],
                    'start_time'     => $start,
                    'end_time'       => $end,
                    'status'         => 'completed',
                    'created_at'     => $start,
                    'updated_at'     => $end,
                ]);
            }
        }
    }
}
