<?php

namespace App\app\Domains\Appointments\Models;

use App\app\Domains\Appointments\Enuns\StatusAppointment;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'date',
    'status',
])]
class Appointment extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'status' => StatusAppointment::class,
        ];
    }
}
