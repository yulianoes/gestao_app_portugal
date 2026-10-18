<?php

namespace App\Domains\Appointments\ViewModels;

use App\Domains\Appointments\Models\Appointment;

final class AppointmentShowViewModel
{
    public function __construct(private readonly Appointment $appointment) {}

    /**
     * @return array{name: string, email: string, date: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->appointment->name,
            'email' => $this->appointment->email,
            'date' => $this->appointment->date->format('d/m/Y H:i'),
            'status' => $this->appointment->status->label(),
        ];
    }
}
