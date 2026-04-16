<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('register', function (Request $request) {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_country' => 'required|string|max:10',
            'phone_local'   => 'required|string|max:20',
            'password'      => 'required|confirmed|min:8',
        ], [
            'phone_local.required' => 'رقم الهاتف مطلوب.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone_country . $request->phone_local,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    })->name('register.action');

    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $role = Auth::user()->role;
            $intended = match($role) {
                'admin'    => '/admin/dashboard',
                'operator' => '/operator/dashboard',
                default    => '/dashboard',
            };
            return redirect()->intended($intended);
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة.',
        ]);
    })->name('login.action');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
?>
