<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_id' => 'required|exists:bills,id',
            'customer_name' => 'nullable|string',
            'tax_id' => 'nullable|string',
        ];
    }
}
