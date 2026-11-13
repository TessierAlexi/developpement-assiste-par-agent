<?php

namespace App\Models;

use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Thrown when the recalls of a vehicle cannot be looked up, with a message meant for the user.
 */
class RecallLookupFailed extends RuntimeException implements ShouldntReport
{
    /**
     * NHTSA could not be reached or did not answer properly.
     */
    public static function unavailable(): self
    {
        return new self(
            'The NHTSA recall service is unavailable right now. Try again later.',
            Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }

    /**
     * Render the failure as a JSON response carrying its message.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
