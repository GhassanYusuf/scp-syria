<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarRegistryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plate_number' => $this->plate_number,
            'entry_time' => $this->entry_time,
            'exit_time' => $this->exit_time,
            'is_active' => $this->active,
            'duration_hours' => $this->entry_time ? $this->entry_time->diffInHours(now()) : null,
            'parking_lot' => new ParkingLotResource($this->whenLoaded('parkingLot')),
        ];
    }
}
?>
