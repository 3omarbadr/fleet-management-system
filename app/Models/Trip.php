<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'name',
        'origin_station_id',
        'destination_station_id',
    ];

    /**
     * Get the origin station.
     */
    public function originStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'origin_station_id');
    }

    /**
     * Get the destination station.
     */
    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'destination_station_id');
    }

    /**
     * Get the trip stops ordered by order.
     */
    public function tripStops(): HasMany
    {
        return $this->hasMany(TripStop::class)->orderBy('order');
    }

    /**
     * Get the scheduled trips for this trip.
     */
    public function scheduledTrips(): HasMany
    {
        return $this->hasMany(ScheduledTrip::class);
    }
}
