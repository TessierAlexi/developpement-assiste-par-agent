<?php

namespace App\Http\Requests;

use App\Enums\CarStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a change of a car's inventory status.
 */
class UpdateCarStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(CarStatus::class)],
        ];
    }

    /**
     * Get the requested status.
     */
    public function status(): CarStatus
    {
        return $this->enum('status', CarStatus::class);
    }
}
