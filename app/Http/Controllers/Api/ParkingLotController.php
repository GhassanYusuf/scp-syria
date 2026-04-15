<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Http\Resources\ParkingLotResource;
use App\Models\Booking;
use App\Models\CarRegistry;
use Illuminate\Http\Request;

class ParkingLotController extends Controller
{
    public function index(Request $request)
    {
        $query = ParkingLot::query()
                           ->withCount([
                               'carRegistries as active_registries_count' => function($q) {
                                   $q->active();
                               },
                               'bookings as active_bookings_count' => function($q) {
                                   $q->where('status', 'active');
                               }
                           ]);

        $lots = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => ParkingLotResource::collection($lots),
                'current_page' => $lots->currentPage(),
                'last_page' => $lots->lastPage(),
                'per_page' => $lots->perPage(),
                'total' => $lots->total()
            ],
            'message' => 'Parking lots retrieved successfully',
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $query = ParkingLot::query()
                           ->withCount([
                               'carRegistries as active_registries_count' => function($q) {
                                   $q->active();
                               },
                               'bookings as active_bookings_count' => function($q) {
                                   $q->where('status', 'active');
                               }
                           ])
                           ->search($q);

        $lots = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => ParkingLotResource::collection($lots),
                'current_page' => $lots->currentPage(),
                'last_page' => $lots->lastPage(),
                'per_page' => $lots->perPage(),
                'total' => $lots->total()
            ],
            'message' => 'Parking lots search results',
        ]);
    }

    public function show(ParkingLot $parkingLot)
    {
        $parkingLot->loadCount([
            'carRegistries as active_registries_count' => function($q) {
                $q->active();
            },
            'bookings as active_bookings_count' => function($q) {
                $q->where('status', 'active');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => new ParkingLotResource($parkingLot),
            'message' => 'Parking lot details retrieved',
        ]);
    }

    public function status(ParkingLot $parkingLot)
    {
        $activeRegistries = $parkingLot->carRegistries()->active()->count();
        $activeBookings = $parkingLot->bookings()->where('status', 'active')->count();
        $occupied = $activeRegistries + $activeBookings;
        $available = max(0, $parkingLot->total_capacity - $occupied);
        $usagePercentage = $parkingLot->total_capacity > 0 ? round(($occupied / $parkingLot->total_capacity) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $parkingLot->id,
                'name' => $parkingLot->name,
                'total_capacity' => $parkingLot->total_capacity,
                'occupied_spaces' => $occupied,
                'available_spaces' => $available,
                'usage_percentage' => $usagePercentage,
                'active_registries_count' => $activeRegistries,
                'active_bookings_count' => $activeBookings,
                'status' => $available === 0 ? 'full' : ($available < $parkingLot->total_capacity * 0.2 ? 'limited' : 'available'),
                'active_cars' => $parkingLot->carRegistries()->active()->pluck('plate_number')->toArray(),
                'active_bookings' => $parkingLot->bookings()->where('status', 'active')->get(['id', 'customer_name', 'phone', 'start_time', 'end_time'])->toArray(),
            ],
            'message' => 'Parking lot status retrieved successfully',
        ]);
    }
}
?>
