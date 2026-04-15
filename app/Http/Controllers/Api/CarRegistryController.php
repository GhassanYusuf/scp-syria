<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperatorCheckInRequest;
use App\Models\CarRegistry;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CarRegistryController extends Controller
{
    public function checkIn(StoreOperatorCheckInRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $parkingLot = ParkingLot::findOrFail($validated['parking_lot_id']);

        // Check capacity vs active registries
        $activeCount = $parkingLot->carRegistries()->active()->count();
        if ($activeCount >= $parkingLot->total_capacity) {
            return response()->json([
                'success' => false,
                'message' => 'Parking lot is full',
            ], 422);
        }

        $registry = CarRegistry::create([
            'parking_lot_id' => $validated['parking_lot_id'],
            'plate_number' => $validated['vehicle_plate'],
            'entry_time' => now(),
            // Optional: predicted exit based on duration
            'exit_time' => isset($validated['duration_hours']) ? now()->addHours($validated['duration_hours']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Car entry registered successfully',
            'data' => $registry->load('parkingLot'),
        ], 201);
    }

    public function checkOut(Request $request, CarRegistry $carRegistry): JsonResponse
    {
        if (! $carRegistry->exit_time) {
            $carRegistry->update([
                'exit_time' => now(),
            ]);

            $durationHours = $carRegistry->entry_time->diffInHours(now());
            $parkingLot = $carRegistry->parkingLot;
            $fee = ceil($durationHours) * $parkingLot->price_per_hour;

            return response()->json([
                'success' => true,
                'message' => 'Car exit registered successfully',
                'data' => [
                    'duration_hours' => round($durationHours, 2),
                    'fee' => $fee,
                    'plate_number' => $carRegistry->plate_number,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Car already checked out',
        ], 400);
    }
}
?>
