<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function activeIndex(Request $request): \Illuminate\View\View
    {
        $parkingLotId = $request->get('parking_lot_id');
        $search       = $request->get('search');
        $parkingLots  = ParkingLot::active()->get(['id', 'name']);

        $base = Booking::with('parkingLot')->where('status', 'active');

        // Summary stats (always across all lots/filters)
        $stats = [
            'total'       => (clone $base)->count(),
            'walkin'      => (clone $base)->where('source', 'walk_in')->count(),
            'reservation' => (clone $base)->where('source', 'reservation')->count(),
            'overdue'     => (clone $base)->where('end_time', '<', now())->count(),
        ];

        // Filtered query
        $query = (clone $base)->orderBy('start_time', 'asc');

        if ($parkingLotId) {
            $query->where('parking_lot_id', $parkingLotId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_plate', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $activeBookings = $query->paginate(50);

        return view('admin.bookings.active', compact(
            'activeBookings', 'parkingLots', 'parkingLotId', 'stats', 'search'
        ));
    }

    public function completeBooking(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'الحجز غير نشط'
            ], 400);
        }

        $parkingLot = $booking->parkingLot;
        $actualDurationHours = $booking->start_time->diffInHours(now());
        $actualFee = ceil($actualDurationHours) * $parkingLot->price_per_hour;

        $booking->update([
            'status' => 'completed',
            'end_time' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء الحجز بنجاح',
            'data' => [
                'actual_duration' => $actualDurationHours . ' ساعات',
                'actual_fee' => number_format($actualFee, 2) . ' ' . config('app.currency', 'ريال'),
                'vehicle_plate' => $booking->vehicle_plate ?? $booking->customer_name
            ]
        ]);
    }
}
?>

