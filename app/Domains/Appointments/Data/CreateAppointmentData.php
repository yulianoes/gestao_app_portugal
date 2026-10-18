<?php

namespace App\Domains\Appointments\Data;

use App\Domains\Appointments\Enums\StatusAppointment;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class CreateAppointmentData extends Data
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
