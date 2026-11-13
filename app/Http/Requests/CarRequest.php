<?php

namespace App\Http\Requests;

use App\Models\Car;
use App\Models\CarEquipment;
use App\Models\CarStatus;
use App\Models\Drivetrain;
use App\Models\Vin;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validates the car details sent by the add and edit forms, which share every rule.
 */
class CarRequest extends FormRequest
{
    /**
     * Tidy the VIN before it is validated, so one typed in lowercase is accepted.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('vin'))) {
            $this->merge(['vin' => Str::upper(trim($this->input('vin')))]);
        }
    }

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
            'vin' => [
                'nullable', 'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! Vin::isValid($value)) {
                        $fail('The VIN must be '.Vin::LENGTH.' characters, without I, O or Q, and have a valid check digit.');
                    }
                },
                Rule::unique(Car::class)->ignore($this->route('car')),
            ],
            'status' => ['required', Rule::enum(CarStatus::class)],
            'received_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'year' => ['required', 'integer', 'between:1900,'.(now()->year + 1)],
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'trim' => ['required', 'string', 'max:255'],
            'body_type' => ['required', 'string', 'max:255'],
            'doors' => ['nullable', 'integer', 'between:2,5'],
            'seats' => ['nullable', 'integer', 'between:1,15'],
            'colour' => ['nullable', 'string', 'max:255'],
            'interior_colour' => ['nullable', 'string', 'max:255'],
            'engine' => ['nullable', 'string', 'max:255'],
            'engine_displacement_l' => ['nullable', 'numeric', 'decimal:0,1', 'between:0.5,10'],
            'cylinders' => ['nullable', 'integer', 'between:2,16'],
            'transmission' => ['required', 'string', 'max:255'],
            'drivetrain' => ['nullable', Rule::enum(Drivetrain::class)],
            'fuel_type' => ['required', 'string', 'max:255'],
            'city_l_per_100km' => ['nullable', 'numeric', 'decimal:0,1', 'between:1,40'],
            'highway_l_per_100km' => ['nullable', 'numeric', 'decimal:0,1', 'between:1,40'],
            'mileage_km' => ['required', 'integer', 'min:0', 'max:2000000'],
            'price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'cost_price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'equipment' => ['array'],
            'equipment.*' => ['string', 'distinct', Rule::in(CarEquipment::keys())],
            'photo_url' => ['nullable', 'url', 'max:2048'],
            'photo_source_url' => ['nullable', 'url', 'max:2048'],
            'photo_author' => ['nullable', 'string', 'max:255'],
            'photo_licence' => ['nullable', 'string', 'max:255'],
            'photo_licence_url' => ['nullable', 'url', 'max:2048'],
            'photo_attribution_required' => ['boolean'],
        ];
    }

    /**
     * Get the names shown in error messages for fields whose key reads poorly.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vin' => 'VIN',
            'engine_displacement_l' => 'engine displacement',
            'city_l_per_100km' => 'city consumption',
            'highway_l_per_100km' => 'highway consumption',
            'equipment.*' => 'equipment option',
        ];
    }

    /**
     * Get the car details to save, without the status, which follows its own rules.
     *
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->safe()->except('status');
    }

    /**
     * Get the status chosen in the form.
     */
    public function status(): CarStatus
    {
        return $this->enum('status', CarStatus::class);
    }
}
