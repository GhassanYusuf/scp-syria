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

    public function checkoutPreview(Booking $booking): JsonResponse
    {
        if ($booking->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'الحجز غير نشط'], 400);
        }

        $lot      = $booking->parkingLot;
        $start    = $booking->start_time;
        $end      = now();
        $duration = $start->diffInMinutes($end);
        $calc     = $lot->calculateFee($start, $end, $booking->pricing_snapshot);

        return response()->json([
            'success' => true,
            'data'    => [
                'plate'          => $booking->vehicle_plate,
                'customer_name'  => $booking->customer_name,
                'lot_name'       => $lot->name,
                'entry_time'     => $start->format('Y/m/d H:i'),
                'exit_time'      => $end->format('Y/m/d H:i'),
                'duration_label' => floor($duration / 60) . 'س ' . ($duration % 60) . 'د',
                'fee_details'    => $calc['details'],
                'total_fee'      => $calc['total'],
            ],
        ]);
    }

    public function completeBooking(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'الحجز غير نشط'], 400);
        }

        $end  = now();
        $type = $request->input('type', 'force'); // 'payment' | 'force'

        if ($type === 'payment') {
            $request->validate([
                'payment_method' => 'required|in:cash,upload',
                'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            ]);

            $lot   = $booking->parkingLot;
            $calc  = $lot->calculateFee($booking->start_time, $end, $booking->pricing_snapshot);

            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }

            $booking->update([
                'status'         => 'completed',
                'end_time'       => $end,
                'total_fee'      => $calc['total'],
                'payment_method' => $request->payment_method,
                'payment_proof'  => $proofPath,
                'paid_at'        => $end,
            ]);
        } else {
            // Force close — car left without paying
            $booking->update([
                'status'   => 'completed',
                'end_time' => $end,
                'notes'    => $request->input('notes') ?: null,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'تم إنهاء الحجز بنجاح']);
    }
}
?>

