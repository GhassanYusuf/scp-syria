<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parking_lot_id' => $this->parking_lot_id,
            'parking_lot' => $this->whenLoaded('parkingLot', fn() => [
                'id'   => $this->parkingLot->id,
                'name' => $this->parkingLot->name,
            ]),
            'customer_name' => $this->customer_name,
            'phone' => $this->phone,
            'start_time' => $this->start_time->toIso8601String(),
            'end_time' => $this->end_time->toIso8601String(),
            'status' => $this->status,
        ];
    }
}
?>

