<?php

namespace App\Http\Requests;

use App\Models\Inspection;
use App\Models\Setting;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_phone')) {
            $this->merge([
                'customer_phone' => preg_replace('/[\s\-()]+/', '', (string) $this->input('customer_phone')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $timeSlots = Setting::list('inspection_time_slots', '9am-11am,11am-1pm,1pm-3pm,3pm-5pm');

        return [
            'inspectable_type' => ['required', 'string', Rule::in(array_keys(Inspection::INSPECTABLE_TYPES))],
            'inspectable_id' => ['required', 'integer'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time_slot' => ['required', 'string', Rule::in($timeSlots)],
            'alternate_date' => ['nullable', 'date', 'after_or_equal:today'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('inspectable_type');
            $class = Inspection::INSPECTABLE_TYPES[$type] ?? null;

            if ($class && ! $class::query()->whereKey($this->input('inspectable_id'))->exists()) {
                $validator->errors()->add('inspectable_id', 'The selected property could not be found.');
            }

            $allowedDays = Setting::list('inspection_days', 'Mon,Tue,Wed,Thu,Fri,Sat');
            $preferred = $this->input('preferred_date');

            if ($preferred && $allowedDays && ! $validator->errors()->has('preferred_date')) {
                try {
                    if (! in_array(Carbon::parse($preferred)->format('D'), $allowedDays, true)) {
                        $validator->errors()->add(
                            'preferred_date',
                            'Inspections are not available on the day you selected.',
                        );
                    }
                } catch (\Exception) {
                    // The base date rule already reports unparseable values.
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Enter a valid Nigerian phone number.',
            'inspectable_type.in' => 'This item cannot be booked for inspection.',
        ];
    }
}
