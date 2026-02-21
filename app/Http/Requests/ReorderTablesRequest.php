<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderTablesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', \App\Models\Table::first() ?? new \App\Models\Table());
    }

    public function rules(): array
    {
        return [
            'rooms' => 'required|array',
            'rooms.*.id' => 'required|exists:rooms,id',
            'rooms.*.sort_order' => 'required|integer|min:0',
            'rooms.*.tables' => 'present|array',
            'rooms.*.tables.*.id' => 'required|exists:tables,id',
            'rooms.*.tables.*.sort_order' => 'required|integer|min:0',
            'unassigned' => 'present|array',
            'unassigned.*.id' => 'required|exists:tables,id',
            'unassigned.*.sort_order' => 'required|integer|min:0',
        ];
    }
}
