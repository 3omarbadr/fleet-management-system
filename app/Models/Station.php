<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get trips that originate from this station.
     */
    public function tripsAsOrigin(): HasMany
    {
        return $this->hasMany(Trip::class, 'origin_station_id');
    }

    /**
     * Get trips that end at this station.
     */
    public function tripsAsDestination(): HasMany
    {
        return $this->hasMany(Trip::class, 'destination_station_id');
    }

    /**
     * Get trip stops at this station.
     */
    public function tripStops(): HasMany
    {
        return $this->hasMany(TripStop::class);
    }

    /**
     * Get bookings that start from this station.
     */
    public function bookingsAsStart(): HasMany
    {
        return $this->hasMany(Booking::class, 'start_station_id');
    }

    /**
     * Get bookings that end at this station.
     */
    public function bookingsAsEnd(): HasMany
    {
        return $this->hasMany(Booking::class, 'end_station_id');
    }
}
