<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
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
            'name' => $this->name,
            'origin_station' => [
                'id' => $this->origin_station_id,
                'name' => $this->whenLoaded('originStation', fn() => $this->originStation->name),
                'city' => $this->whenLoaded('originStation', fn() => $this->originStation->city),
            ],
            'destination_station' => [
                'id' => $this->destination_station_id,
                'name' => $this->whenLoaded('destinationStation', fn() => $this->destinationStation->name),
                'city' => $this->whenLoaded('destinationStation', fn() => $this->destinationStation->city),
            ],
            'stops' => TripStopResource::collection($this->whenLoaded('tripStops')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
