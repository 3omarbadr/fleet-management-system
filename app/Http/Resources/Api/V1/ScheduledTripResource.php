<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'departure_time' => $this->departure_time->format('Y-m-d H:i:s'),
            'arrival_time' => $this->arrival_time->format('Y-m-d H:i:s'),
            'trip' => new TripResource($this->whenLoaded('trip')),
            'bus' => [
                'id' => $this->bus_id,
                'name' => $this->whenLoaded('bus', fn() => $this->bus->name),
                'license_plate' => $this->whenLoaded('bus', fn() => $this->bus->license_plate),
                'capacity' => $this->whenLoaded('bus', fn() => $this->bus->capacity),
            ],
            'available_seats' => $this->when(isset($this->available_seats), $this->available_seats),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
