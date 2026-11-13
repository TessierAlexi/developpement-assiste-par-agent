<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * The figures behind the inventory dashboard.
 *
 * A car is "in inventory" until it is sold: available and on-hold cars both count.
 */
class InventoryDashboardService
{
    /**
     * Number of days a car should spend in inventory at most, on average.
     */
    public const TARGET_DAYS_IN_INVENTORY = 60;

    /**
     * Number of cars the dealership aims to sell each month.
     */
    public const MONTHLY_SALES_TARGET = 10;

    /**
     * Number of cars shown in the list of the oldest cars in inventory.
     */
    protected const OLDEST_CARS_SHOWN = 5;

    /**
     * Get everything the dashboard shows: its gauges, its tiles and the oldest cars in inventory.
     *
     * @return array{
     *     gauges: array{
     *         availableShare: array{value: ?int, target: int},
     *         averageDaysInInventory: array{value: ?int, target: int},
     *         salesThisMonth: array{value: int, target: int},
     *     },
     *     tiles: array{
     *         availableInventoryValue: int,
     *         countsByStatus: list<array{value: string, label: string, count: int}>,
     *         averageSoldMargin: ?int,
     *     },
     *     oldestCars: list<array<string, mixed>>,
     * }
     */
    public function summary(): array
    {
        return [
            'gauges' => [
                'availableShare' => ['value' => $this->availableShare(), 'target' => 100],
                'averageDaysInInventory' => ['value' => $this->averageDaysInInventory(), 'target' => self::TARGET_DAYS_IN_INVENTORY],
                'salesThisMonth' => ['value' => $this->salesThisMonth(), 'target' => self::MONTHLY_SALES_TARGET],
            ],
            'tiles' => [
                'availableInventoryValue' => $this->availableInventoryValue(),
                'countsByStatus' => $this->countsByStatus(),
                'averageSoldMargin' => $this->averageSoldMargin(),
            ],
            'oldestCars' => $this->oldestCars(),
        ];
    }

    /**
     * Get the percentage of the cars in inventory that are available, or null when the inventory is empty.
     */
    public function availableShare(): ?int
    {
        $inInventory = $this->carsInInventory()->count();

        if ($inInventory === 0) {
            return null;
        }

        $available = Car::query()->withStatus(CarStatus::Available)->count();

        return (int) round($available / $inInventory * 100);
    }

    /**
     * Get the average number of days the cars in inventory have been there, or null when the inventory is empty.
     */
    public function averageDaysInInventory(): ?int
    {
        $averageDays = $this->carsInInventory()
            ->pluck('received_on')
            ->avg(fn (Carbon $receivedOn) => $this->daysSince($receivedOn));

        return $averageDays === null ? null : (int) round($averageDays);
    }

    /**
     * Get the number of cars sold since the start of the current month.
     */
    public function salesThisMonth(): int
    {
        return Car::query()
            ->withStatus(CarStatus::Sold)
            ->whereBetween('sold_on', [today()->startOfMonth(), today()->endOfMonth()])
            ->count();
    }

    /**
     * Get the total asking price, in dollars, of the available cars that have one.
     */
    public function availableInventoryValue(): int
    {
        return (int) Car::query()->withStatus(CarStatus::Available)->sum('price');
    }

    /**
     * Get the number of cars in each status, in the order the statuses are declared.
     *
     * @return list<array{value: string, label: string, count: int}>
     */
    public function countsByStatus(): array
    {
        $counts = Car::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return array_map(
            fn (CarStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ],
            CarStatus::cases(),
        );
    }

    /**
     * Get the average margin, in dollars, of the sold cars: asking price minus cost price.
     * Cars missing either price are left out; null when no sold car has both.
     */
    public function averageSoldMargin(): ?int
    {
        $averageMargin = Car::query()
            ->withStatus(CarStatus::Sold)
            ->whereNotNull('price')
            ->whereNotNull('cost_price')
            ->get(['id', 'price', 'cost_price'])
            ->avg(fn (Car $car) => $car->price - $car->cost_price);

        return $averageMargin === null ? null : (int) round($averageMargin);
    }

    /**
     * Get the cars that have been in inventory the longest, oldest first.
     *
     * @return list<array{
     *     id: int,
     *     stock_number: string,
     *     status: string,
     *     year: int,
     *     make: string,
     *     model: string,
     *     photo_url: ?string,
     *     received_on: string,
     *     days_in_inventory: int,
     * }>
     */
    public function oldestCars(): array
    {
        return $this->carsInInventory()
            ->oldest('received_on')
            ->oldest('id')
            ->limit(self::OLDEST_CARS_SHOWN)
            ->get(['id', 'stock_number', 'status', 'year', 'make', 'model', 'photo_url', 'received_on'])
            ->map(fn (Car $car) => [
                'id' => $car->id,
                'stock_number' => $car->stock_number,
                'status' => $car->status->value,
                'year' => $car->year,
                'make' => $car->make,
                'model' => $car->model,
                'photo_url' => $car->photo_url,
                'received_on' => $car->received_on->toDateString(),
                'days_in_inventory' => $this->daysSince($car->received_on),
            ])
            ->all();
    }

    /**
     * Start a query for the cars that are not sold yet.
     *
     * @return Builder<Car>
     */
    protected function carsInInventory(): Builder
    {
        return Car::query()->whereNot('status', CarStatus::Sold);
    }

    /**
     * Get the number of whole days between a date and today, never less than zero.
     */
    protected function daysSince(Carbon $date): int
    {
        return max(0, (int) $date->diffInDays(today()));
    }
}
