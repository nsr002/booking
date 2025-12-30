<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'credits_remaining',
        'purchased_at',
        'expires_at',
        'payment_slip_path',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the package.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package details.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get bookings using this package.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if package is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if package has credits.
     */
    public function hasCredits(): bool
    {
        return $this->credits_remaining > 0;
    }

    /**
     * Check if package is valid (approved, not expired, has credits).
     */
    public function isValid(): bool
    {
        return $this->payment_status === 'approved' 
            && !$this->isExpired() 
            && $this->hasCredits();
    }
}
