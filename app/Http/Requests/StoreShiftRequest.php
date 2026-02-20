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

    /**
     * Auto-fill start_time/end_time from ShiftType defaults if not provided.
     */
    protected function prepareForValidation(): void
    {
        $shiftType = ShiftType::tryFrom($this->input('shift_type'));

        if ($shiftType && !$this->filled('start_time')) {
            $this->merge(['start_time' => $shiftType->startTime()]);
        }
        if ($shiftType && !$this->filled('end_time')) {
            $this->merge(['end_time' => $shiftType->endTime()]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'shift_type' => [
                'required',
                Rule::enum(ShiftType::class),
                Rule::unique('shifts')->where(fn ($query) => $query
                    ->where('user_id', $this->input('user_id'))
                    ->where('date', $this->input('date'))
                ),
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'shift_type.unique' => 'This user already has this shift type scheduled for the selected date.',
        ];
    }
}
