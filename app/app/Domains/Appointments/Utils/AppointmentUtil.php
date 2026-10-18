<?php

namespace App\app\Domains\Appointments\Utils;

use App\Domains\Appointments\Enuns\StatusAppointment;

class AppointmentUtil
{
     public function getStatusAppointment(): string
    {
        if (enum_exists(StatusAppointment::class)) {
            $cases = (new \ReflectionEnum(StatusAppointment::class))->getCases();

            return implode(',', array_map(
                fn (\ReflectionEnumBackedCase $case) => $case->getBackingValue(),
                $cases
            ));
        }

        $constants = (new \ReflectionClass(StatusAppointment::class))->getConstants();

        return implode(',', array_values($constants));
    }
}
