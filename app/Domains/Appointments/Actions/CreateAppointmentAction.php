<?php

namespace App\Domains\Appointments\Actions;

use App\Domains\Appointments\Data\CreateAppointmentData;
use App\Domains\Appointments\Exceptions\AppointmentExceptions;
use App\Domains\Appointments\Mail\AppointmentConfirmed;
use App\Domains\Appointments\Models\Appointment;
use App\Domains\Appointments\Repositories\AppointmentRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class CreateAppointmentAction
{
    public function __construct(private readonly AppointmentRepository $appointmentRepository) {}

    /**
     * "isSlotTaken" gives a friendly error in the normal case; the unique index on
     * "booked_slot" is what actually stops two simultaneous requests for the same slot.
     */
    public function execute(CreateAppointmentData $data): Appointment
    {
        try {
            $appointment = DB::transaction(function () use ($data): Appointment {
                if ($this->appointmentRepository->isSlotTaken($data->date)) {
                    throw AppointmentExceptions::slotAlreadyTaken();
                }

                return $this->appointmentRepository->create([
                    'name' => $data->name,
                    'email' => $data->email,
                    'phone' => $data->phone,
                    'date' => $data->date,
                    'status' => $data->status,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw AppointmentExceptions::slotAlreadyTaken();
        }

        // Sent to the email given in the form (the visitor may not be logged in).
        Mail::to($appointment->email, $appointment->name)
            ->queue(new AppointmentConfirmed($appointment));

        return $appointment;
    }
}
