<?php

namespace App\Http\Requests;

use App\Models\Car;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the car details sent by the add and edit forms.
 */
class CarRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stock_number' => [
                'required', 'string', 'max:20', 'alpha_dash:ascii',
                Rule::unique(Car::class)->ignore($this->route('car')),
            ],
            'received_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'year' => ['required', 'integer', 'between:1900,'.(now()->year + 1)],
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'trim' => ['required', 'string', 'max:255'],
            'body_type' => ['required', 'string', 'max:255'],
            'fuel_type' => ['required', 'string', 'max:255'],
            'transmission' => ['required', 'string', 'max:255'],
            'colour' => ['nullable', 'string', 'max:255'],
            'mileage_km' => ['required', 'integer', 'min:0', 'max:2000000'],
            'price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'photo_url' => ['nullable', 'url', 'max:2048'],
            'photo_source_url' => ['nullable', 'url', 'max:2048'],
            'photo_author' => ['nullable', 'string', 'max:255'],
            'photo_licence' => ['nullable', 'string', 'max:255'],
            'photo_licence_url' => ['nullable', 'url', 'max:2048'],
            'photo_attribution_required' => ['boolean'],
        ];
    }
}
