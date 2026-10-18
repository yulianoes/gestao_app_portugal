<?php

namespace App\Domains\Appointments\Http\Requests;

use App\Domains\Appointments\Data\CreateAppointmentData;
use App\Domains\Appointments\Enums\StatusAppointment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'date' => ['required', 'date', 'after:now'],
            'status' => ['sometimes', Rule::enum(StatusAppointment::class)],
        ];
    }

    public function messages(): array
    {
        $allowedStatuses = implode(', ', array_column(StatusAppointment::cases(), 'value'));

        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'phone.required' => 'The phone field is required.',
            'phone.max' => 'The phone may not be greater than 20 characters.',
            'phone.regex' => 'The phone must contain only digits, spaces, "+", "-" or parentheses.',
            'date.required' => 'The date field is required.',
            'date.date' => 'The date must be a valid date.',
            'date.after' => 'The date must be in the future.',
            'status.enum' => 'The selected status is invalid. Allowed values are: ' . $allowedStatuses,
        ];
    }

    public function toData(): CreateAppointmentData
    {
        return CreateAppointmentData::from([
            ...$this->validated(),
            // "date" accepts any format strtotime understands (e.g. "2026-10-19T10:00" from <input type="datetime-local">).
            'date' => CarbonImmutable::parse($this->validated('date')),
        ]);
    }
}
