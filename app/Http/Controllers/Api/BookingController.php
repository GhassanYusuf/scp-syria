<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreBookingRequest $request)
    {
        $validated = $request->validated();

        $lot = \App\Models\ParkingLot::find($validated['parking_lot_id']);
        $validated['pricing_snapshot'] = $lot?->pricingSnapshot();
        $validated['user_id'] = $request->user()->id;

        $booking = \App\Models\Booking::create($validated);

        // Note: In production, create CarRegistry here for the booking car

        return response()->json([
            'success' => true,
            'data' => new BookingResource($booking),
            'message' => 'Booking created successfully',
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Booking::with('parkingLot')->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('parking_lot_id')) {
            $query->where('parking_lot_id', $request->parking_lot_id);
        }

        $bookings = $query->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => [
                'data' => BookingResource::collection($bookings),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
            'message' => 'Recent bookings retrieved successfully',
        ]);
    }
}

