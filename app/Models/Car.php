<?php

namespace App\Models;

use App\Enums\CarStatus;
use App\Enums\Drivetrain;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'stock_number', 'vin', 'received_on',
    'year', 'make', 'model', 'trim', 'body_type', 'doors', 'seats', 'colour', 'interior_colour',
    'engine', 'engine_displacement_l', 'cylinders', 'transmission', 'drivetrain', 'fuel_type',
    'city_l_per_100km', 'highway_l_per_100km',
    'mileage_km', 'price', 'cost_price', 'description', 'internal_notes', 'equipment',
    'photo_url', 'photo_source_url', 'photo_author', 'photo_licence', 'photo_licence_url', 'photo_attribution_required',
])]
class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CarStatus::class,
            'received_on' => 'date:Y-m-d',
            'sold_on' => 'date:Y-m-d',
            'year' => 'integer',
            'mileage_km' => 'integer',
            'price' => 'integer',
            'cost_price' => 'integer',
            'drivetrain' => Drivetrain::class,
            'engine_displacement_l' => 'decimal:1',
            'cylinders' => 'integer',
            'doors' => 'integer',
            'seats' => 'integer',
            'city_l_per_100km' => 'decimal:1',
            'highway_l_per_100km' => 'decimal:1',
            'equipment' => 'array',
            'photo_attribution_required' => 'boolean',
        ];
    }

    /**
     * Get the history of the car's status changes.
     *
     * @return HasMany<CarStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(CarStatusChange::class);
    }

    /**
     * Limit the query to cars whose make, model or trim contains the search term.
     *
     * @param  Builder<Car>  $query
     */
    public function scopeMatching(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $query) use ($search) {
            foreach (['make', 'model', 'trim'] as $column) {
                $query->orWhereLike($column, '%'.$search.'%');
            }
        });
    }

    /**
     * Limit the query to cars with the given status, if any.
     *
     * @param  Builder<Car>  $query
     */
    public function scopeWithStatus(Builder $query, ?CarStatus $status): void
    {
        if ($status === null) {
            return;
        }

        $query->where('status', $status);
    }
}
