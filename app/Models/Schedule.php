<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'date',
        'start_time',
        'end_time',
        'max_bookings',
        'current_bookings',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get the trainer for this schedule.
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    /**
     * Get the bookings for this schedule.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if schedule is full.
     */
    public function isFull(): bool
    {
        return $this->current_bookings >= $this->max_bookings;
    }

    /**
     * Check if schedule can be booked.
     */
    public function canBeBooked(): bool
    {
        return $this->is_available && !$this->isFull() && $this->date >= now()->toDateString();
    }
}
