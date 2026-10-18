<?php

namespace App\Domains\Appointments\Models;

use App\Domains\Appointments\Enums\StatusAppointment;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'date',
    'status',
])]
final class Appointment extends Model
{
    /**
     * Keep "booked_slot" in sync with "date": it is cleared on cancellation so the slot
     * becomes free again, and its unique index blocks double bookings at database level.
     */
    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment): void {
            $appointment->booked_slot = $appointment->status === StatusAppointment::CANCELLED
                ? null
                : $appointment->date;
        });
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'booked_slot' => 'datetime',
            'status' => StatusAppointment::class,
        ];
    }
}
