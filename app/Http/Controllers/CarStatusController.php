<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCarStatusRequest;
use App\Models\Car;
use App\Models\CarInventoryService;
use Illuminate\Http\RedirectResponse;

class CarStatusController extends Controller
{
    /**
     * Move a car to another inventory status.
     */
    public function update(UpdateCarStatusRequest $request, Car $car, CarInventoryService $inventory): RedirectResponse
    {
        $inventory->changeStatus($car, $request->status(), $request->user());

        return back();
    }
}
