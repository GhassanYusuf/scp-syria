<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OperatorController extends Controller
{
    // ── Lot picker + panel ──────────────────────────────────────────────────────

    public function dashboard(Request $request): \Illuminate\View\View
    {
        $user           = Auth::user();
        $assignedLotId  = $user->parking_lot_id; // null = no restriction (e.g. admin)

        $rawLots = ParkingLot::active()->withStatus()->get();

        $parkingLots = $rawLots->map(fn($lot) => [
            'id'       => $lot->id,
            'name'     => $lot->name,
            'address'  => $lot->address,
            'total'    => $lot->total_capacity,
            'avail'    => max(0, $lot->total_capacity - ($lot->active_bookings_count + $lot->active_registries_count)),
            'occupied' => $lot->active_bookings_count + $lot->active_registries_count,
            'price'    => (float) $lot->price_per_hour,
            'hours'    => $lot->working_hours,
            'lat'      => (float) $lot->latitude,
            'lng'      => (float) $lot->longitude,
            'image'    => $lot->image ? Storage::url($lot->image) : null,
            'locked'   => $assignedLotId !== null && $lot->id !== $assignedLotId,
        ])->values();

        // If operator has an assigned lot, force that lot
        $selectedLotId = $request->get('lot_id');
        if ($assignedLotId !== null) {
            // Reject any attempt to view a different lot
            if ($selectedLotId && (int) $selectedLotId !== $assignedLotId) {
                return redirect()->route('operator.dashboard', ['lot_id' => $assignedLotId]);
            }
            // Auto-select assigned lot if nothing selected
            if (!$selectedLotId) {
                $selectedLotId = $assignedLotId;
            }
        }

        $selectedLot    = null;
        $activeCars     = collect();
        $reservations   = collect();

        if ($selectedLotId) {
            $selectedLot = ParkingLot::active()->findOrFail($selectedLotId);

            // Walk-in cars currently inside (source=walk_in, status=active)
            $activeCars = Booking::where('parking_lot_id', $selectedLotId)
                ->where('source', 'walk_in')
                ->where('status', 'active')
                ->latest('start_time')
                ->get();

            // Pre-reservations not yet activated (source=reservation, status=active)
            $reservations = Booking::where('parking_lot_id', $selectedLotId)
                ->where('source', 'reservation')
                ->where('status', 'active')
                ->orderBy('start_time')
                ->get();
        }

        return view('operator.dashboard', compact(
            'parkingLots', 'selectedLot', 'activeCars', 'reservations', 'selectedLotId', 'assignedLotId'
        ));
    }

    // ── Walk-in check-in ────────────────────────────────────────────────────────

    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parking_lot_id' => 'required|exists:parking_lots,id',
            'vehicle_plate'  => 'required|string|max:50',
            'customer_name'  => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'duration_hours' => 'required|numeric|min:0.25|max:72',
        ]);

        $lot = ParkingLot::findOrFail($data['parking_lot_id']);

        $activeCount = $lot->bookings()->where('status', 'active')->count();
        if ($activeCount >= $lot->total_capacity) {
            return response()->json(['success' => false, 'message' => 'الموقف ممتلئ حالياً'], 422);
        }

        $booking = Booking::create([
            'parking_lot_id'   => $lot->id,
            'vehicle_plate'    => $data['vehicle_plate'],
            'customer_name'    => $data['customer_name'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'source'           => 'walk_in',
            'start_time'       => now(),
            'end_time'         => now()->addHours((float) $data['duration_hours']),
            'status'           => 'active',
            'pricing_snapshot' => $lot->pricingSnapshot(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل دخول السيارة بنجاح',
            'data'    => ['id' => $booking->id, 'plate' => $booking->vehicle_plate],
        ]);
    }

    // ── Activate a reservation (open parking for reserved car) ─────────────────

    public function activateReservation(Booking $booking): JsonResponse
    {
        if ($booking->source !== 'reservation' || $booking->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'الحجز غير صالح للتفعيل'], 400);
        }

        $booking->update([
            'source'     => 'walk_in',  // now treated as an active walk-in
            'start_time' => now(),
            'end_time'   => now()->addHours(
                max(1, now()->diffInHours($booking->end_time, false))
            ),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تفعيل الحجز وفتح بوابة الدخول']);
    }

    // ── Checkout: calculate fee and return receipt data ─────────────────────────

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
                'booking_id'    => $booking->id,
                'plate'         => $booking->vehicle_plate,
                'customer_name' => $booking->customer_name,
                'lot_name'      => $lot->name,
                'entry_time'    => $start->format('Y/m/d H:i'),
                'exit_time'     => $end->format('Y/m/d H:i'),
                'duration_min'  => $duration,
                'duration_label'=> floor($duration / 60) . 'س ' . ($duration % 60) . 'د',
                'fee_details'   => $calc['details'],
                'total_fee'     => $calc['total'],
            ],
        ]);
    }

    // ── Process payment and complete booking ─────────────────────────────────────

    public function processPayment(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate([
            'payment_method' => 'required|in:cash,upload',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if ($booking->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'الحجز غير نشط'], 400);
        }

        $lot   = $booking->parkingLot;
        $start = $booking->start_time;
        $end   = now();
        $calc  = $lot->calculateFee($start, $end, $booking->pricing_snapshot);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')
                ->store('payment_proofs', 'public');
        }

        $booking->update([
            'status'         => 'completed',
            'end_time'       => $end,
            'total_fee'      => $calc['total'],
            'payment_method' => $data['payment_method'],
            'payment_proof'  => $proofPath,
            'paid_at'        => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج وإتمام الدفع بنجاح',
            'data'    => [
                'total_fee'      => $calc['total'],
                'payment_method' => $data['payment_method'],
            ],
        ]);
    }

    // ── Cancel a pre-reservation (operator verifies name or phone) ──────────────

    public function cancelReservation(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->source !== 'reservation' || $booking->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'هذا الحجز لا يمكن إلغاؤه'], 400);
        }

        $input = trim($request->input('verification', ''));

        $nameMatch  = $booking->customer_name && mb_strtolower($input) === mb_strtolower($booking->customer_name);
        $phoneMatch = $booking->phone         && $input === $booking->phone;

        if (!$nameMatch && !$phoneMatch) {
            return response()->json(['success' => false, 'message' => 'الاسم أو رقم الهاتف غير مطابق'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'message' => 'تم إلغاء الحجز بنجاح']);
    }

    // ── Legacy checkout (kept for backward compatibility) ───────────────────────

    public function checkOut(Request $request, Booking $booking): JsonResponse
    {
        return $this->processPayment($request->merge(['payment_method' => 'cash']), $booking);
    }
}
