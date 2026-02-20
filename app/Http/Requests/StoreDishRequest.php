<?php

namespace App\Http\Requests;

use App\Enums\DishCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category' => ['required', Rule::enum(DishCategory::class)],
        ];
    }
}
