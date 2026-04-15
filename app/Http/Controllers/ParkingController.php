<?php

namespace App\Http\Controllers;

use App\Models\ParkingLot;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    public function index()
    {
        $lots = ParkingLot::active()
            ->withStatus()
            ->get()
            ->map(fn($lot) => [
                'id'      => $lot->id,
                'name'    => $lot->name,
                'address' => $lot->address,
                'total'   => $lot->total_capacity,
                'avail'   => max(0, $lot->total_capacity - $lot->active_registries_count - $lot->active_bookings_count),
                'price'   => (float) $lot->price_per_hour,
                'hours'   => $lot->working_hours ?? '24/7',
                'lat'     => (float) $lot->latitude,
                'lng'     => (float) $lot->longitude,
            ]);

        return view('index', compact('lots'));
    }
}
