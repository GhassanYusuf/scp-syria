<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function statsJson(Request $request)
    {
        // Total metrics
        $totalParkingLots = ParkingLot::count();
        $totalBookings = Booking::count();

        // Active bookings (assume status = 'active'; adjust if using time overlap)
        $activeBookings = Booking::where('status', 'active')->count();

        // Occupancy rate: (active bookings / total capacity) * 100
        $totalCapacity = ParkingLot::sum('total_capacity');
        $occupancyRate = $totalCapacity > 0 ? round(($activeBookings / $totalCapacity) * 100, 1) : 0;

        // Estimated revenue: sum price_per_hour for all active bookings
        $revenue = Booking::where('status', 'active')
            ->join('parking_lots', 'bookings.parking_lot_id', '=', 'parking_lots.id')
            ->sum('parking_lots.price_per_hour');

        // Available spots (total capacity - active)
        $availableSpots = $totalCapacity - $activeBookings;

        return response()->json([
            'success' => true,
            'data' => [
                'total_parking_lots' => $totalParkingLots,
                'total_bookings' => $totalBookings,
                'active_bookings' => $activeBookings,
                'occupancy_rate' => $occupancyRate,
                'estimated_revenue' => round($revenue, 2),
                'available_spots' => $availableSpots,
            ]
        ]);
    }

    public function chartsJson(Request $request)
    {
        $now = Carbon::now();

        // Daily bookings last 7 days
        $dailyBookings = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$now->clone()->subDays(7), $now])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing days
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->clone()->subDays($i)->format('Y-m-d');
            $dates[$date] = $dailyBookings[$date] ?? 0;
        }
        ksort($dates);

        // Top 5 parking lots by total bookings
        $topLots = ParkingLot::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get(['id', 'name', 'bookings_count']);

        // Occupancy trend: current vs previous day (simplified)
        $todayActive = Booking::where('status', 'active')->whereDate('created_at', $now->format('Y-m-d'))->count();
        $yesterdayActive = Booking::where('status', 'active')->whereDate('created_at', $now->clone()->subDay()->format('Y-m-d'))->count();
        $trend = $todayActive - $yesterdayActive; // positive growth

        return response()->json([
            'success' => true,
            'data' => [
                'daily_bookings' => array_values($dates),
                'top_parking_lots' => $topLots->map(fn($lot) => ['id' => $lot->id, 'name' => $lot->name, 'value' => $lot->bookings_count])->toArray(),
                'daily_dates'      => array_keys($dates),
                'occupancy_trend' => $trend,
            ]
        ]);
    }
}
?>

