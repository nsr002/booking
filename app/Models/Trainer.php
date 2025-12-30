<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'specialty',
        'bio',
        'avatar',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user associated with the trainer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trainer's schedules.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
