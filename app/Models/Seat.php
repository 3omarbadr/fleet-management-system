<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    protected $fillable = [
        'bus_id',
        'seat_number',
    ];

    /**
     * Get the bus that owns this seat.
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the bookings for this seat.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
