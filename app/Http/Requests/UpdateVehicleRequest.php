<?php

namespace App\Http\Requests;

class UpdateVehicleRequest extends StoreVehicleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Existing listings keep their photos; new uploads are optional.
        $rules['images'] = ['nullable', 'array', 'max:24'];

        return $rules;
    }
}
