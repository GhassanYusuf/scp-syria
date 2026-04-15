<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Booking;

class ProfileController extends Controller
{
    public function show()
    {
        return view('user.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validateWithBag('updateName', [
            'name' => 'required|string|max:255',
        ]);

        Auth::user()->update(['name' => $validated['name']]);

        return back()->with('success', 'تم تحديث الاسم بنجاح.');
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
