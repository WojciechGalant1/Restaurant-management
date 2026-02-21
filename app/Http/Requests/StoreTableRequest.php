<?php

namespace App\Http\Requests;

use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_number' => 'required|integer|unique:tables,table_number',
            'capacity' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(TableStatus::class)],
            'room_id' => 'nullable|exists:rooms,id',
        ];
    }
}
