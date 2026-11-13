<?php

namespace App\Models;

use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'year', 'make', 'model', 'trim', 'body_type', 'fuel_type', 'transmission', 'colour',
    'mileage_km', 'price', 'description',
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
            'year' => 'integer',
            'mileage_km' => 'integer',
            'price' => 'integer',
            'photo_attribution_required' => 'boolean',
        ];
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
}
