<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'customer_name' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ];
    }
}
