<?php

namespace App\app\Domains\Appointments\Http\Controllers;

use App\app\Domains\Appointments\Actions\CreateAppointmentAction;
use App\app\Domains\Appointments\Http\Requests\StoreAppointmentRequest;
use App\app\Domains\Appointments\ViewModels\AppointmentShowViewModel;

class AppointmentController
{
    public function __construct(
        private AppointmentShowViewModel $appointmentShowViewModel
    ) {
    }

    public function store(StoreAppointmentRequest $request, CreateAppointmentAction $createAppointmentAction)
    {
        $appointment = $createAppointmentAction->execute($request->toData());

        return $this->appointmentShowViewModel->showAppointment($appointment);
    }
}
