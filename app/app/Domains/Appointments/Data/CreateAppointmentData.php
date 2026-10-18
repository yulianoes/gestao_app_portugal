<?php

namespace App\app\Domains\Appointments\Data;

use App\app\Domains\Appointments\Enuns\StatusAppointment;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class CreateAppointmentData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly CarbonImmutable $date,
        public readonly StatusAppointment $status = StatusAppointment::PENDING,
    ) {
    }
}
