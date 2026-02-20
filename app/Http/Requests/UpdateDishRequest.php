<?php

namespace App\Http\Requests;

use App\Enums\DishCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'category' => ['sometimes', Rule::enum(DishCategory::class)],
        ];
    }
}
