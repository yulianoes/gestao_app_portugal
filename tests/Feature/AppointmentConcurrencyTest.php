<?php

namespace Tests\Feature;

use App\Domains\Appointments\Enums\StatusAppointment;
use App\Domains\Appointments\Models\Appointment;
use App\Domains\Appointments\Repositories\AppointmentRepository;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentConcurrencyTest extends TestCase
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

    /**
     * Simulates two simultaneous requests: both run "isSlotTaken" before either has inserted,
     * so the check says the slot is free and only the unique index can stop the second one.
     */
    public function test_the_database_rejects_a_second_booking_that_slipped_past_the_slot_check(): void
    {
        Mail::fake();
        $this->app->instance(AppointmentRepository::class, new class extends AppointmentRepository
        {
            public function isSlotTaken(CarbonInterface $date): bool
            {
                return false;
            }
        });

        $this->post(route('appointments.store'), $this->payload())->assertOk();

        $this->post(route('appointments.store'), $this->payload(['email' => 'other@example.com']))
            ->assertSessionHasErrors('date');

        $this->assertSame(1, Appointment::count());
        Mail::assertQueuedCount(1);
    }

    public function test_a_cancelled_appointment_frees_its_slot(): void
    {
        Mail::fake();

        $this->post(route('appointments.store'), $this->payload())->assertOk();
        Appointment::sole()->update(['status' => StatusAppointment::CANCELLED]);

        $this->post(route('appointments.store'), $this->payload(['email' => 'other@example.com']))
            ->assertOk();

        $this->assertSame(2, Appointment::count());
    }
}
