<?php

namespace App\Models;

use Database\Factories\CarStatusChangeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry of a car's history: who moved it from one status to another, and when.
 */
#[Fillable(['user_id', 'from_status', 'to_status'])]
class CarStatusChange extends Model
{
    /** @use HasFactory<CarStatusChangeFactory> */
    use HasFactory;

    /**
     * A change is never edited, so it only has a creation date.
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => CarStatus::class,
            'to_status' => CarStatus::class,
        ];
    }

    /**
     * Get the car whose status changed.
     *
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the user who changed the status, if their account still exists.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
