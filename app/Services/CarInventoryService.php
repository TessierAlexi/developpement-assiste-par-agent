<?php

namespace App\Services;

use App\Enums\CarStatus;
use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Business rules for adding, editing, selling and removing cars.
 *
 * The status and sale date only change through changeStatus(), never through
 * the car details, so a sold car keeps its sale date however it is edited.
 */
class CarInventoryService
{
    /**
     * Add a new car to the inventory; it starts out available unless another status is given.
     * Its first status is not a change, so nothing goes into its history.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function add(array $attributes, CarStatus $status = CarStatus::Available): Car
    {
        $car = new Car($attributes);
        $car->status = CarStatus::Available;
        $car->sold_on = null;

        $this->applyStatus($car, $status);
        $car->save();

        return $car;
    }

    /**
     * Update the details of a car, leaving its status and sale date alone unless a status is given.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Car $car, array $attributes, ?CarStatus $status = null, ?User $changedBy = null): Car
    {
        $car->fill($attributes);

        return $this->changeStatus($car, $status ?? $car->status, $changedBy);
    }

    /**
     * Move a car to a new status, recording today as its sale date when it is sold
     * and adding the change, with the user who made it, to the car's history.
     */
    public function changeStatus(Car $car, CarStatus $status, ?User $changedBy = null): Car
    {
        $previousStatus = $car->status;
        $this->applyStatus($car, $status);

        DB::transaction(function () use ($car, $previousStatus, $status, $changedBy) {
            $car->save();

            if ($previousStatus !== $status) {
                $car->statusChanges()->create([
                    'user_id' => $changedBy?->id,
                    'from_status' => $previousStatus,
                    'to_status' => $status,
                ]);
            }
        });

        return $car;
    }

    /**
     * Set the status of a car and the sale date that goes with it, without saving.
     */
    protected function applyStatus(Car $car, CarStatus $status): void
    {
        $car->sold_on = match (true) {
            $status !== CarStatus::Sold => null,
            $car->status === CarStatus::Sold => $car->sold_on,
            default => today(),
        };
        $car->status = $status;
    }

    /**
     * Remove a car from the inventory.
     */
    public function remove(Car $car): void
    {
        $car->delete();
    }
}
