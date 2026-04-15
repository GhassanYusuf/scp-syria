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
        $parkingLots = ParkingLot::active()->get(['id', 'name']);

        $query = Booking::with('parkingLot')
            ->where('status', 'active')
            ->orderBy('start_time', 'desc');

        if ($parkingLotId) {
            $query->where('parking_lot_id', $parkingLotId);
        }

        $activeBookings = $query->paginate(50);

        return view('admin.bookings.active', compact('activeBookings', 'parkingLots', 'parkingLotId'));
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

