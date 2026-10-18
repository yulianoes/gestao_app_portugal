<?php

namespace App\app\Domains\Appointments\ViewModels;

use App\app\Domains\Appointments\Models\Appointment;

class AppointmentShowViewModel
{
    public function showAppointment(Appointment $appointment)
    {
        return view('appointments.show', ['appointment' => $appointment]);
    }
}
