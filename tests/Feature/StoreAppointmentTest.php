<?php

namespace Tests\Feature;

use App\Domains\Appointments\Enums\StatusAppointment;
use App\Domains\Appointments\Mail\AppointmentConfirmed;
use App\Domains\Appointments\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StoreAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'phone' => '+351 912 345 678',
            'date' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
        ], $overrides);
    }

    public function test_it_creates_the_appointment_and_queues_the_confirmation_email(): void
    {
        Mail::fake();

        $this->post(route('appointments.store'), $this->payload())
            ->assertOk()
            ->assertSee('Appointment booked');

        $appointment = Appointment::sole();
        $this->assertSame(StatusAppointment::PENDING, $appointment->status);

        Mail::assertQueued(AppointmentConfirmed::class, function (AppointmentConfirmed $mail) use ($appointment) {
            return $mail->hasTo('ana@example.com') && $mail->appointment->is($appointment);
        });
    }

    public function test_the_confirmation_email_renders(): void
    {
        $appointment = Appointment::create($this->payload(['status' => StatusAppointment::PENDING]));

        (new AppointmentConfirmed($appointment))
            ->assertSeeInHtml('Ana Silva')
            ->assertSeeInHtml($appointment->date->format('d/m/Y'));
    }

    public function test_the_same_email_can_book_more_than_once(): void
    {
        Mail::fake();

        $this->post(route('appointments.store'), $this->payload())->assertOk();
        $this->post(route('appointments.store'), $this->payload([
            'date' => now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
        ]))->assertOk();

        $this->assertSame(2, Appointment::count());
    }

    public function test_it_rejects_a_slot_that_is_already_booked(): void
    {
        Mail::fake();

        $this->post(route('appointments.store'), $this->payload())->assertOk();

        $this->post(route('appointments.store'), $this->payload(['email' => 'other@example.com']))
            ->assertSessionHasErrors('date');

        $this->assertSame(1, Appointment::count());
        Mail::assertQueuedCount(1);
    }

    public function test_it_validates_the_input(): void
    {
        Mail::fake();

        $this->post(route('appointments.store'), [
            'email' => 'not-an-email',
            'phone' => 'abc',
            'date' => now()->subDay()->toDateTimeString(),
            'status' => 'occupied',
        ])->assertSessionHasErrors([
            'name' => 'The name field is required.',
            'email' => 'The email must be a valid email address.',
            'phone' => 'The phone must contain only digits, spaces, "+", "-" or parentheses.',
            'date' => 'The date must be in the future.',
            'status' => 'The selected status is invalid. Allowed values are: pending, confirmed, cancelled',
        ]);

        $this->assertSame(0, Appointment::count());
        Mail::assertNothingQueued();
    }
}
