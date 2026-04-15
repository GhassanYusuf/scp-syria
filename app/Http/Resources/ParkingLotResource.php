<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingLotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'total_capacity' => $this->total_capacity,
            'occupied_spaces' => $this->occupied_spaces,
            'available_spaces' => $this->available_spaces,
            'usage_percentage' => $this->usage_percentage,
            'active_registries_count' => $this->active_registries_count ?? 0,
            'active_bookings_count' => $this->active_bookings_count ?? 0,
            'price_per_hour' => $this->price_per_hour,
            'working_hours' => $this->working_hours,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'status' => $this->getAvailabilityStatus(),
        ];
    }

    private function getAvailabilityStatus()
    {
        $available = $this->available_spaces;
        $total = $this->total_capacity;

        if ($available === 0) return 'full';
        if ($available < $total * 0.2) return 'limited';
        return 'available';
    }
}
?>
