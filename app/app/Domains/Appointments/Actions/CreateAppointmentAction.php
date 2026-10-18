<?php

namespace App\app\Domains\Appointments\Actions;

use App\app\Domains\Appointments\Data\CreateAppointmentData;
use App\app\Domains\Appointments\Exceptions\AppointmentExceptions;
use App\app\Domains\Appointments\Mail\AppointmentConfirmed;
use App\app\Domains\Appointments\Models\Appointment;
use App\app\Domains\Appointments\Repositories\AppointmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateAppointmentAction
{
    public function __construct(private readonly AppointmentRepository $appointmentRepository) {}

    public function execute(CreateAppointmentData $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            if ($this->appointmentRepository->isSlotTaken($data->date)) {
                throw AppointmentExceptions::slotAlreadyTaken();
            }

            $appointment = $this->appointmentRepository->create([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'date' => $data->date,
                'status' => $data->status,
            ]);

            // Sent to the email given in the form (the visitor may not be logged in).
            Mail::to($appointment->email, $appointment->name)
                ->queue(new AppointmentConfirmed($appointment));

            return $appointment;
        });
    }
}
