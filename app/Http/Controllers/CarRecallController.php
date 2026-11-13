<?php

namespace App\Http\Controllers;

use App\Exceptions\RecallLookupFailed;
use App\Models\Car;
use App\Services\SafetyRecallService;
use Illuminate\Http\JsonResponse;

class CarRecallController extends Controller
{
    /**
     * List the safety recalls NHTSA has for the make, model and year of a car, for its detail page to show.
     *
     * @throws RecallLookupFailed rendered as a JSON error the page can show
     */
    public function show(Car $car, SafetyRecallService $recalls): JsonResponse
    {
        return response()->json([
            'recalls' => $recalls->forVehicle($car->make, $car->model, $car->year),
        ]);
    }
}
