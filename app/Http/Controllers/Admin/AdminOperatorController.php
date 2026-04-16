<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminOperatorController extends Controller
{
    public function index()
    {
        $operators = User::where('role', 'operator')
            ->with('assignedLot')
            ->orderBy('name')
            ->get();

        $lots = ParkingLot::orderBy('name')->get();

        return view('admin.operators.index', compact('operators', 'lots'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'nullable|string|max:20',
            'password'       => ['required', Password::min(8)],
            'parking_lot_id' => 'nullable|exists:parking_lots,id',
        ], [
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
        ]);

        User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'password'       => Hash::make($data['password']),
            'role'           => 'operator',
            'parking_lot_id' => $data['parking_lot_id'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'تم إنشاء حساب المشغّل بنجاح.']);
    }

    public function update(Request $request, User $operator)
    {
        if ($operator->role !== 'operator') {
            return response()->json(['success' => false, 'message' => 'المستخدم ليس مشغّلاً.'], 403);
        }

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $operator->id,
            'phone'          => 'nullable|string|max:20',
            'password'       => ['nullable', Password::min(8)],
            'parking_lot_id' => 'nullable|exists:parking_lots,id',
        ], [
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',
        ]);

        $updates = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'parking_lot_id' => $data['parking_lot_id'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $operator->update($updates);

        return response()->json(['success' => true, 'message' => 'تم تحديث بيانات المشغّل.']);
    }

    public function destroy(User $operator)
    {
        if ($operator->role !== 'operator') {
            return response()->json(['success' => false, 'message' => 'المستخدم ليس مشغّلاً.'], 403);
        }

        $operator->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المشغّل.']);
    }
}
