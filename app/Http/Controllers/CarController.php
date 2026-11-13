<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarController extends Controller
{
    /**
     * Number of cars shown on each page of the inventory.
     */
    protected const PER_PAGE = 12;

    /**
     * Display the inventory, newest listings first.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $cars = Car::query()
            ->matching($search)
            ->select(['id', 'year', 'make', 'model', 'trim', 'body_type', 'mileage_km', 'price', 'photo_url', 'created_at'])
            ->latest()
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Cars/Index', [
            'cars' => $cars,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Display a single car.
     */
    public function show(Car $car): Response
    {
        return Inertia::render('Cars/Show', [
            'car' => $car,
        ]);
    }
}
