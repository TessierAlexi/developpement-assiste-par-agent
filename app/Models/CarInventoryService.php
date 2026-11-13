<?php

namespace App\Models;

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
     *
     * @param  array<string, mixed>  $attributes
     */
    public function add(array $attributes, CarStatus $status = CarStatus::Available): Car
    {
        $car = new Car($attributes);
        $car->status = CarStatus::Available;
        $car->sold_on = null;

        return $this->changeStatus($car, $status);
    }

    /**
     * Update the details of a car, leaving its status and sale date alone unless a status is given.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Car $car, array $attributes, ?CarStatus $status = null): Car
    {
        $car->fill($attributes);

        return $this->changeStatus($car, $status ?? $car->status);
    }

    /**
     * Move a car to a new status, recording today as its sale date when it is sold.
     */
    public function changeStatus(Car $car, CarStatus $status): Car
    {
        $car->sold_on = match (true) {
            $status !== CarStatus::Sold => null,
            $car->status === CarStatus::Sold => $car->sold_on,
            default => today(),
        };
        $car->status = $status;
        $car->save();

        return $car;
    }

    /**
     * Remove a car from the inventory.
     */
    public function remove(Car $car): void
    {
        $car->delete();
    }
}
