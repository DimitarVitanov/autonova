<?php

namespace App\Http\Requests;

use App\Services\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'make_id' => ['nullable', 'exists:makes,id'],
            'car_model_id' => ['nullable', 'exists:car_models,id'],
            'version' => ['nullable', 'string', 'max:120'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'vat' => ['required', Rule::in(array_keys(config('marketplace.vat')))],
            'year' => ['required', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'mileage_km' => ['required', 'integer', 'min:0', 'max:5000000'],
            'fuel' => ['required', Rule::in(array_keys(config('marketplace.fuels')))],
            'transmission' => ['required', Rule::in(array_keys(config('marketplace.transmissions')))],
            'engine_cc' => ['nullable', 'integer', 'min:0', 'max:30000'],
            'power_hp' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'drivetrain' => ['nullable', Rule::in(array_keys(config('marketplace.drivetrains')))],
            'body_type' => ['nullable', 'string', 'max:60'],
            'doors' => ['nullable', 'integer', 'min:0', 'max:10'],
            'seats' => ['nullable', 'integer', 'min:0', 'max:60'],
            'color' => ['nullable', 'string', 'max:40'],
            'condition' => ['required', Rule::in(array_keys(config('marketplace.conditions')))],
            'owners' => ['nullable', 'integer', 'min:0', 'max:20'],
            'registered_until' => ['nullable', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:60'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:5000'],
            'features' => ['nullable', 'array'],
            'features.*' => ['integer', 'exists:features,id'],
            'promotion' => ['nullable', Rule::in(['none', 'bump', 'featured'])],
            'images' => ['required', 'array', 'min:1', 'max:24'],
            'images.*' => [
                'image', 'mimes:jpeg,jpg,png,webp', 'max:10240',
                Rule::dimensions()
                    ->minWidth(ImageService::MIN_W)->minHeight(ImageService::MIN_H)
                    ->maxWidth(ImageService::MAX_SIDE)->maxHeight(ImageService::MAX_SIDE),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => __('Please add at least one photo of your vehicle.'),
            'images.*.image' => __('Each upload must be a valid image file.'),
            'images.*.mimes' => __('Photos must be JPG, PNG or WEBP.'),
            'images.*.max' => __('Each photo must be 10 MB or smaller.'),
            'images.*.uploaded' => __('A photo failed to upload — it may be too large.'),
            'images.*.dimensions' => __('Each photo must be at least :min and at most :max px on its longest side.', [
                'min' => ImageService::MIN_W . '×' . ImageService::MIN_H,
                'max' => ImageService::MAX_SIDE,
            ]),
        ];
    }
}
