<?php

namespace App\Domains\Appointments\Repositories;

use App\Domains\Appointments\Enums\StatusAppointment;
use App\Domains\Appointments\Models\Appointment;
use Carbon\CarbonInterface;

class AppointmentRepository
{
    /**
     * A slot is taken when there is a non-cancelled appointment at the same date/time.
     */
    public function isSlotTaken(CarbonInterface $date): bool
    {
        return Appointment::where('date', $date)
            ->where('status', '!=', StatusAppointment::CANCELLED)
            ->exists();
    }

    public function create(array $attributes): Appointment
    {
        return Appointment::create($attributes);
    }
}
