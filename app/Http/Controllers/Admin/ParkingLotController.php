<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParkingLotRequest;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ParkingLotController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $parkingLots = ParkingLot::withCount(['bookings as active_bookings_count' => fn($q) => $q->where('status', 'active')])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.parking-lots.index', compact('parkingLots'));
    }

    public function show(ParkingLot $parkingLot): JsonResponse
    {
        $parkingLot->loadCount(['bookings as active_bookings_count' => fn($q) => $q->where('status', 'active')]);

        return response()->json([
            'success' => true,
            'data' => $parkingLot
        ]);
    }

    public function store(StoreParkingLotRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('parking-lots', 'public');
        }

        $parkingLot = ParkingLot::create($data);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة موقف السيارات بنجاح',
            'data' => $parkingLot->loadCount(['bookings as active_bookings_count' => fn($q) => $q->where('status', 'active')])
        ]);
    }

    public function update(StoreParkingLotRequest $request, ParkingLot $parkingLot): JsonResponse
    {
        $data = $request->validated();

        // Never let validated() overwrite the stored image path with null.
        // Only update the image column when a new file is actually uploaded.
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($parkingLot->image) {
                Storage::disk('public')->delete($parkingLot->image);
            }
            $data['image'] = $request->file('image')->store('parking-lots', 'public');
        }

        $parkingLot->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث موقف السيارات بنجاح',
            'data' => $parkingLot->loadCount(['bookings as active_bookings_count' => fn($q) => $q->where('status', 'active')])
        ]);
    }

    public function destroy(ParkingLot $parkingLot): JsonResponse
    {
        // Block deletion if there are active bookings
        $activeCount = $parkingLot->bookings()->where('status', 'active')->count();
        if ($activeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن الحذف — يوجد {$activeCount} حجز نشط في هذا الموقف",
            ], 422);
        }

        if ($parkingLot->image) {
            Storage::disk('public')->delete($parkingLot->image);
        }

        $parkingLot->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الموقف بنجاح',
        ]);
    }

    public function updatePricing(Request $request, ParkingLot $parkingLot): JsonResponse
    {
        $data = $request->validate([
            'price_per_hour' => 'required|numeric|min:0|max:100000',
            'pricing_rules'  => 'nullable|array',
            'pricing_rules.*'=> 'nullable|numeric|min:0|max:100000',
        ]);

        // Build clean rules array: only include days that differ from base price.
        // Keys 1-7 (Mon-Sun). Null or empty string means "use base price".
        $rules = [];
        foreach ($data['pricing_rules'] ?? [] as $day => $rate) {
            if ($rate !== null && $rate !== '' && (float) $rate !== (float) $data['price_per_hour']) {
                $rules[(int) $day] = (float) $rate;
            }
        }

        $parkingLot->update([
            'price_per_hour' => $data['price_per_hour'],
            'pricing_rules'  => empty($rules) ? null : $rules,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ أسعار الموقف بنجاح',
            'data'    => [
                'price_per_hour' => (float) $parkingLot->price_per_hour,
                'pricing_rules'  => $parkingLot->pricing_rules,
            ],
        ]);
    }

    public function toggleStatus(ParkingLot $parkingLot): JsonResponse
    {
        $parkingLot->update(['is_active' => !$parkingLot->is_active]);

        return response()->json([
            'success' => true,
            'message' => $parkingLot->is_active ? 'تم تفعيل الموقف' : 'تم إلغاء تفعيل الموقف',
            'data' => ['is_active' => $parkingLot->is_active]
        ]);
    }
}
?>

