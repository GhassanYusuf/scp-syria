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

        $stats = [
            'total'     => $bookings->count(),
            'active'    => $bookings->where('status', 'active')->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        return view('user.dashboard', compact('bookings', 'stats'));
    }
}
