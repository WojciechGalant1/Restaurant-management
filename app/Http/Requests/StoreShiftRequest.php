<?php

namespace App\Http\Requests;

use App\Enums\ShiftType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $shiftType = ShiftType::tryFrom($this->input('shift_type'));

        if ($shiftType && !$this->filled('start_time')) {
            $this->merge(['start_time' => $shiftType->startTime()]);
        }
        if ($shiftType && !$this->filled('end_time')) {
            $this->merge(['end_time' => $shiftType->endTime()]);
        }

        if ($this->has('user_id') && !$this->has('user_ids')) {
            $this->merge(['user_ids' => [$this->input('user_id')]]);
        }
        if (is_string($this->input('user_ids'))) {
            $this->merge(['user_ids' => array_filter(explode(',', $this->input('user_ids')))]);
        }
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => 'required|exists:users,id',
            'date' => 'required|date',
            'replicate_days' => 'nullable|array',
            'replicate_days.*' => 'integer|min:1|max:7',
            'shift_type' => [
                'required',
                Rule::enum(ShiftType::class),
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => __('Select at least one staff member.'),
        ];
    }
}
