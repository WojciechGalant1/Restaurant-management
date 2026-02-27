<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id' => 'sometimes|exists:tables,id',
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'items' => 'sometimes|array|min:1',
            'items.*.id' => 'sometimes|nullable|exists:order_items,id',
            'items.*.menu_item_id' => 'required_with:items|exists:menu_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric',
            'items.*.notes' => 'nullable|string',
            'items.*.cancel_action' => 'sometimes|nullable|in:voided,cancelled',
            'items.*.cancel_reason' => 'required_if:items.*.cancel_action,voided,items.*.cancel_action,cancelled|nullable|string|max:500',
        ];
    }
}
