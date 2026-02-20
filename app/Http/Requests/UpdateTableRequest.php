<?php

namespace App\Http\Requests;

use App\Enums\TableStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'capacity' => 'sometimes|integer|min:1',
            'status' => ['sometimes', Rule::enum(TableStatus::class)],
            'waiter_id' => [
                'sometimes',
                'nullable',
                Rule::exists('users', 'id')->where('role', UserRole::Waiter->value),
            ],
        ];
    }
}
