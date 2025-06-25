<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledTrip extends Model
{
    protected $fillable = [
        'trip_id',
        'bus_id',
        'departure_time',
        'arrival_time',
        'status',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    /**
     * Get the trip for this scheduled trip.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the bus for this scheduled trip.
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the bookings for this scheduled trip.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
