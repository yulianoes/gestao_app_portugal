<?php

namespace App\Domains\Appointments\Exceptions;

use Illuminate\Validation\ValidationException;

final class AppointmentExceptions
{
    public static function appointmentNotFound(): \Exception
    {
        return new \Exception('Appointment not found.');
    }

    /**
     * Thrown as a validation error so the user is sent back to the form
     * with the message attached to the "date" field.
     */
    public static function slotAlreadyTaken(): ValidationException
    {
        return ValidationException::withMessages([
            'date' => 'This date and time is already booked. Please choose another one.',
        ]);
    }
}
