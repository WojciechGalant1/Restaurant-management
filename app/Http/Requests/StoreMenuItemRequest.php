<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dish_id' => 'required|exists:dishes,id',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
        ];
    }
}
