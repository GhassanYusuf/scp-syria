<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Booking;
use App\Models\ParkingLot;

class ProfileController extends Controller
{
    public function show()
    {
        return view('user.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $request->validateWithBag('updateName', [
            'name'          => 'required|string|max:255',
            'phone_country' => 'required|string|max:10',
            'phone_local'   => 'required|string|max:20',
        ], [
            'phone_local.required' => 'رقم الهاتف مطلوب.',
        ]);

        Auth::user()->update([
            'name'  => $request->name,
            'phone' => $request->phone_country . $request->phone_local,
        ]);

        return back()->with('success', 'تم تحديث البيانات بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $request->validateWithBag('updatePassword', [
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(
                ['current_password' => 'كلمة السر الحالية غير صحيحة.'],
                'updatePassword'
            );
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'تم تغيير كلمة السر بنجاح.');
    }

    public function reserve(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'parking_lot_id' => 'required|exists:parking_lots,id',
            'vehicle_plate'  => 'required|string|max:20',
            'start_time'     => 'required|date|after:now',
            'end_time'       => 'required|date|after:start_time',
        ]);

        $lot = ParkingLot::findOrFail($request->parking_lot_id);

        // Check capacity
        $active = $lot->carRegistries()->active()->count()
                + $lot->bookings()->where('status', 'active')->count();
        if ($active >= $lot->total_capacity) {
            return response()->json([
                'success' => false,
                'message' => 'الموقف ممتلئ حالياً.',
            ], 409);
        }

        $booking = Booking::create([
            'user_id'          => $user->id,
            'parking_lot_id'   => $lot->id,
            'customer_name'    => $user->name,
            'phone'            => $user->phone ?? '',
            'vehicle_plate'    => $request->vehicle_plate,
            'source'           => 'reservation',
            'start_time'       => $request->start_time,
            'end_time'         => $request->end_time,
            'status'           => 'active',
            'pricing_snapshot' => $lot->pricingSnapshot(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الحجز بنجاح.',
            'data'    => ['id' => $booking->id],
        ], 201);
    }

    public function dashboard()
    {
        $bookings = Booking::with('parkingLot')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $pendingDebt = $bookings
            ->where('status', 'cancelled')
            ->where('total_fee', '>', 0)
            ->filter(fn($b) => is_null($b->paid_at))
            ->sum('total_fee');

        $stats = [
            'total'     => $bookings->count(),
            'active'    => $bookings->where('status', 'active')->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        return view('user.dashboard', compact('bookings', 'stats', 'pendingDebt'));
    }

    // ── Cancel preview — returns fee (or free) ────────────────────────────────

    public function cancelPreview(Booking $booking): \Illuminate\Http\JsonResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }
        if ($booking->status !== 'active' || $booking->source !== 'reservation') {
            return response()->json(['success' => false, 'message' => 'لا يمكن إلغاء هذا الحجز'], 400);
        }

        $isFree = now()->lt($booking->start_time);

        if ($isFree) {
            return response()->json([
                'success' => true,
                'data'    => ['is_free' => true, 'fee' => 0, 'fee_details' => []],
            ]);
        }

        $lot      = $booking->parkingLot;
        $calcEnd  = now()->lt($booking->end_time) ? now() : $booking->end_time;
        $calc     = $lot->calculateFee($booking->start_time, $calcEnd, $booking->pricing_snapshot);

        return response()->json([
            'success' => true,
            'data'    => [
                'is_free'     => false,
                'fee'         => $calc['total'],
                'fee_details' => $calc['details'],
                'entry_time'  => $booking->start_time->format('Y/m/d H:i'),
                'cancel_time' => now()->format('Y/m/d H:i'),
            ],
        ]);
    }

    // ── Process cancellation ──────────────────────────────────────────────────

    public function cancelBooking(Request $request, Booking $booking): \Illuminate\Http\JsonResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }
        if ($booking->status !== 'active' || $booking->source !== 'reservation') {
            return response()->json(['success' => false, 'message' => 'لا يمكن إلغاء هذا الحجز'], 400);
        }

        $isFree = now()->lt($booking->start_time);
        $end    = now();

        if ($isFree) {
            $booking->update(['status' => 'cancelled', 'total_fee' => 0, 'end_time' => $end]);
            return response()->json(['success' => true, 'message' => 'تم إلغاء الحجز مجاناً']);
        }

        // After start time — fee applies
        $lot     = $booking->parkingLot;
        $calcEnd = $end->lt($booking->end_time) ? $end : $booking->end_time;
        $calc    = $lot->calculateFee($booking->start_time, $calcEnd, $booking->pricing_snapshot);
        $type    = $request->input('type'); // 'pay_now' | 'pay_later'

        if ($type === 'pay_now') {
            $request->validate(['payment_method' => 'required|in:cash,upload']);
            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }
            $booking->update([
                'status'         => 'cancelled',
                'end_time'       => $end,
                'total_fee'      => $calc['total'],
                'payment_method' => $request->payment_method,
                'payment_proof'  => $proofPath,
                'paid_at'        => $end,
            ]);
            return response()->json(['success' => true, 'message' => 'تم إلغاء الحجز وتسجيل الدفع']);
        }

        // Pay later — record fee as debt
        $booking->update([
            'status'    => 'cancelled',
            'end_time'  => $end,
            'total_fee' => $calc['total'],
        ]);
        return response()->json([
            'success' => true,
            'message' => 'تم الإلغاء. الرسوم المستحقة ' . number_format($calc['total']) . ' ل.س ستُضاف لحجزك القادم.',
            'data'    => ['pending_fee' => $calc['total']],
        ]);
    }

    // ── Pending debt (AJAX for booking modal) ─────────────────────────────────

    public function pendingDebt(): \Illuminate\Http\JsonResponse
    {
        $debt = Booking::where('user_id', Auth::id())
            ->where('status', 'cancelled')
            ->where('total_fee', '>', 0)
            ->whereNull('paid_at')
            ->sum('total_fee');

        return response()->json(['success' => true, 'data' => ['debt' => (float) $debt]]);
    }
}
