<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'scheduled_trip_id',
        'seat_id',
        'start_station_id',
        'end_station_id',
        'status',
    ];

    /**
     * Get the user that owns this booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scheduled trip for this booking.
     */
    public function scheduledTrip(): BelongsTo
    {
        return $this->belongsTo(ScheduledTrip::class);
    }

    /**
     * Get the seat for this booking.
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Get the start station for this booking.
     */
    public function startStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'start_station_id');
    }

    /**
     * Get the end station for this booking.
     */
    public function endStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'end_station_id');
    }
}
