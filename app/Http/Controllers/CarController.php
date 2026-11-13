<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Models\Car;
use App\Models\CarInventoryService;
use App\Models\CarStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarController extends Controller
{
    /**
     * Number of cars shown on each page of the inventory.
     */
    protected const PER_PAGE = 12;

    public function __construct(protected CarInventoryService $inventory) {}

    /**
     * Display the inventory, newest listings first.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->enum('status', CarStatus::class);

        $cars = Car::query()
            ->matching($search)
            ->withStatus($status)
            ->select(['id', 'stock_number', 'status', 'year', 'make', 'model', 'trim', 'body_type', 'mileage_km', 'price', 'photo_url', 'created_at'])
            ->latest()
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Cars/Index', [
            'cars' => $cars,
            'filters' => ['search' => $search, 'status' => $status?->value],
            'statuses' => CarStatus::options(),
        ]);
    }

    /**
     * Show the form for adding a car to the inventory.
     */
    public function create(): Response
    {
        return Inertia::render('Cars/Create', [
            'defaults' => ['received_on' => today()->toDateString()],
        ]);
    }

    /**
     * Add a car to the inventory.
     */
    public function store(CarRequest $request): RedirectResponse
    {
        $car = $this->inventory->add($request->validated());

        return to_route('cars.show', $car);
    }

    /**
     * Display a single car.
     */
    public function show(Car $car): Response
    {
        return Inertia::render('Cars/Show', [
            'car' => $car,
            'statuses' => CarStatus::options(),
        ]);
    }

    /**
     * Show the form for editing a car's details.
     */
    public function edit(Car $car): Response
    {
        return Inertia::render('Cars/Edit', [
            'car' => $car,
        ]);
    }

    /**
     * Save a car's details.
     */
    public function update(CarRequest $request, Car $car): RedirectResponse
    {
        $this->inventory->update($car, $request->validated());

        return to_route('cars.show', $car);
    }

    /**
     * Remove a car from the inventory.
     */
    public function destroy(Car $car): RedirectResponse
    {
        $this->inventory->remove($car);

        return to_route('cars.index');
    }
}
