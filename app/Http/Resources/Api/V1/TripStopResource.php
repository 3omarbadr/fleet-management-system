<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripStopResource extends JsonResource
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
            'stop_order' => $this->stop_order,
            'arrival_time' => $this->arrival_time?->format('H:i:s'),
            'departure_time' => $this->departure_time?->format('H:i:s'),
            'station' => [
                'id' => $this->station_id,
                'name' => $this->whenLoaded('station', fn() => $this->station->name),
                'city' => $this->whenLoaded('station', fn() => $this->station->city),
            ],
        ];
    }
}
