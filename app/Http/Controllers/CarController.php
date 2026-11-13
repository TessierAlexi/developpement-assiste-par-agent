<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Models\Car;
use App\Models\CarDetailTab;
use App\Models\CarEquipment;
use App\Models\CarInventoryService;
use App\Models\CarStatus;
use App\Models\CarStatusChange;
use App\Models\Drivetrain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            'defaults' => [
                'received_on' => today()->toDateString(),
                'status' => CarStatus::Available->value,
            ],
            ...$this->formOptions(),
        ]);
    }

    /**
     * Add a car to the inventory.
     */
    public function store(CarRequest $request): RedirectResponse
    {
        $car = $this->inventory->add($request->details(), $request->status());

        return to_route('cars.show', $car);
    }

    /**
     * Display a single car, opened on the tab named in the URL or on its overview.
     */
    public function show(Request $request, Car $car): Response
    {
        $activeTab = $request->enum('tab', CarDetailTab::class) ?? CarDetailTab::Overview;

        return Inertia::render('Cars/Show', [
            'car' => $car,
            'statuses' => CarStatus::options(),
            'tabs' => CarDetailTab::options(),
            'activeTab' => $activeTab->value,
            'equipment' => CarEquipment::grouped($car->equipment ?? []),
            'statusChanges' => $this->statusHistory($car),
        ]);
    }

    /**
     * Show the form for editing a car's details.
     */
    public function edit(Car $car): Response
    {
        return Inertia::render('Cars/Edit', [
            'car' => $car,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Save a car's details.
     */
    public function update(CarRequest $request, Car $car): RedirectResponse
    {
        $this->inventory->update($car, $request->details(), $request->status(), $request->user());

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

    /**
     * Get the status changes of a car, most recent first, with the name of the user behind each one.
     *
     * @return Collection<int, array{id: int, from: string, to: string, changed_at: string, user: ?string}>
     */
    protected function statusHistory(Car $car): Collection
    {
        return $car->statusChanges()
            ->with('user:id,name')
            ->latest()
            ->latest('id')
            ->get()
            ->map(fn (CarStatusChange $change) => [
                'id' => $change->id,
                'from' => $change->from_status->value,
                'to' => $change->to_status->value,
                'changed_at' => $change->created_at->toIso8601String(),
                'user' => $change->user?->name,
            ]);
    }

    /**
     * Get the choices offered by the selects of the add and edit forms.
     *
     * @return array{
     *     statuses: list<array{value: string, label: string}>,
     *     drivetrains: list<array{value: string, label: string}>,
     *     equipmentCatalogue: list<array{label: string, options: list<array{value: string, label: string}>}>,
     * }
     */
    protected function formOptions(): array
    {
        return [
            'statuses' => CarStatus::options(),
            'drivetrains' => Drivetrain::options(),
            'equipmentCatalogue' => CarEquipment::catalogue(),
        ];
    }
}
