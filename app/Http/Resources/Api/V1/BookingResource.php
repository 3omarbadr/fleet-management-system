<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'status' => $this->status,
            'booked_at' => $this->created_at->format('Y-m-d H:i:s'),
            'trip' => [
                'name' => $this->whenLoaded('scheduledTrip', fn() => $this->scheduledTrip->trip->name),
                'departure_time' => $this->whenLoaded('scheduledTrip', fn() => $this->scheduledTrip->departure_time->format('Y-m-d H:i:s')),
                'arrival_time' => $this->whenLoaded('scheduledTrip', fn() => $this->scheduledTrip->arrival_time->format('Y-m-d H:i:s')),
            ],
            'bus' => [
                'name' => $this->whenLoaded('scheduledTrip', fn() => $this->scheduledTrip->bus->name),
                'license_plate' => $this->whenLoaded('scheduledTrip', fn() => $this->scheduledTrip->bus->license_plate),
            ],
            'seat' => [
                'number' => $this->whenLoaded('seat', fn() => $this->seat->seat_number),
            ],
            'start_station' => [
                'id' => $this->start_station_id,
                'name' => $this->whenLoaded('startStation', fn() => $this->startStation->name),
                'city' => $this->whenLoaded('startStation', fn() => $this->startStation->city),
            ],
            'end_station' => [
                'id' => $this->end_station_id,
                'name' => $this->whenLoaded('endStation', fn() => $this->endStation->name),
                'city' => $this->whenLoaded('endStation', fn() => $this->endStation->city),
            ],
        ];
    }
}
