<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by the policy
    }

    public function rules(): array
    {
        return [
            'slot_minutes' => ['required', 'integer', 'in:30,60,90,120'],
            'days' => ['required', 'array'],
            'days.*.enabled' => ['sometimes', 'boolean'],
            'days.*.start_time' => ['required_if:days.*.enabled,1', 'nullable', 'date_format:H:i'],
            'days.*.end_time' => ['required_if:days.*.enabled,1', 'nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $days = $this->input('days', []);
            $slotMinutes = (int) $this->input('slot_minutes', 60);

            foreach ($days as $dayOfWeek => $day) {
                if (! empty($day['enabled']) && ! empty($day['start_time']) && ! empty($day['end_time'])) {
                    $start = \Carbon\Carbon::createFromFormat('H:i', $day['start_time']);
                    $end = \Carbon\Carbon::createFromFormat('H:i', $day['end_time']);

                    // End time must be after start time
                    if ($end->lte($start)) {
                        $validator->errors()->add(
                            "days.{$dayOfWeek}.end_time",
                            $this->dayName((int) $dayOfWeek) . ': End time must be after start time.'
                        );
                        continue;
                    }

                    // Working block must be at least one slot long
                    $blockMinutes = $start->diffInMinutes($end);
                    if ($blockMinutes < $slotMinutes) {
                        $validator->errors()->add(
                            "days.{$dayOfWeek}.end_time",
                            $this->dayName((int) $dayOfWeek) . ": Working block ({$blockMinutes} min) must be at least one slot long ({$slotMinutes} min)."
                        );
                    }
                }
            }
        });
    }

    private function dayName(int $dayOfWeek): string
    {
        $names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $names[$dayOfWeek] ?? 'Unknown';
    }

    public function messages(): array
    {
        return [
            'slot_minutes.in' => 'Slot length must be 30, 60, 90 or 120 minutes.',
            'days.*.start_time.date_format' => 'Start time must be in HH:MM format.',
            'days.*.end_time.date_format' => 'End time must be in HH:MM format.',
        ];
    }
}
