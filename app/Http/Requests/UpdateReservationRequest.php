<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id' => 'sometimes|exists:tables,id',
            'customer_name' => 'sometimes|string',
            'phone_number' => 'sometimes|string',
            'reservation_date' => 'sometimes|date',
            'party_size' => 'sometimes|integer|min:1',
        ];
    }
}
