<?php

namespace App\Domains\Appointments\Http\Controllers;

use App\Domains\Appointments\Actions\CreateAppointmentAction;
use App\Domains\Appointments\Http\Requests\StoreAppointmentRequest;
use App\Domains\Appointments\ViewModels\AppointmentShowViewModel;
use Illuminate\Contracts\View\View;

final class AppointmentController
{
    public function store(StoreAppointmentRequest $request, CreateAppointmentAction $createAppointmentAction): View
    {
        $appointment = $createAppointmentAction->execute($request->toData());

        return view('appointments.show', (new AppointmentShowViewModel($appointment))->toArray());
    }
}
