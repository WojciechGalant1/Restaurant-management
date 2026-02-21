<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('table'));
    }

    public function rules(): array
    {
        return [
            'shift_id' => ['required', 'exists:shifts,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
