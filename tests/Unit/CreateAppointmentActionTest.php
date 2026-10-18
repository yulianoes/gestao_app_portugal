<?php

namespace Tests\Unit;

use App\Domains\Appointments\Actions\CreateAppointmentAction;
use App\Domains\Appointments\Data\CreateAppointmentData;
use App\Domains\Appointments\Mail\AppointmentConfirmed;
use App\Domains\Appointments\Models\Appointment;
use App\Domains\Appointments\Repositories\AppointmentRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use PDOException;
use Tests\TestCase;

class CreateAppointmentActionTest extends TestCase
{
    private function data(): CreateAppointmentData
    {
        return new CreateAppointmentData(
            name: 'Ana Silva',
            email: 'ana@example.com',
            phone: '+351 912 345 678',
            date: CarbonImmutable::parse('2030-01-10 10:00'),
        );
    }

    private function action(MockInterface $repository): CreateAppointmentAction
    {
        return new CreateAppointmentAction($repository);
    }

    public function test_it_creates_the_appointment_and_queues_the_confirmation_email(): void
    {
        Mail::fake();
        $data = $this->data();
        $appointment = new Appointment([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'date' => $data->date,
            'status' => $data->status,
        ]);

        $repository = Mockery::mock(AppointmentRepository::class);
        $repository->shouldReceive('isSlotTaken')->once()->with($data->date)->andReturnFalse();
        $repository->shouldReceive('create')->once()->with([
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'phone' => '+351 912 345 678',
            'date' => $data->date,
            'status' => $data->status,
        ])->andReturn($appointment);

        $result = $this->action($repository)->execute($data);

        $this->assertSame($appointment, $result);
        Mail::assertQueued(AppointmentConfirmed::class, fn (AppointmentConfirmed $mail) => $mail->hasTo('ana@example.com'));
    }

    public function test_it_rejects_a_taken_slot_without_creating_or_emailing(): void
    {
        Mail::fake();

        $repository = Mockery::mock(AppointmentRepository::class);
        $repository->shouldReceive('isSlotTaken')->once()->andReturnTrue();
        $repository->shouldNotReceive('create');

        try {
            $this->action($repository)->execute($this->data());
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('date', $exception->errors());
        }

        Mail::assertNothingQueued();
    }

    public function test_it_turns_a_unique_index_violation_into_a_slot_error_without_emailing(): void
    {
        Mail::fake();

        $repository = Mockery::mock(AppointmentRepository::class);
        $repository->shouldReceive('isSlotTaken')->once()->andReturnFalse();
        $repository->shouldReceive('create')->once()->andThrow(
            new UniqueConstraintViolationException('sqlite', 'insert into "appointments"', [], new PDOException('UNIQUE constraint failed'))
        );

        try {
            $this->action($repository)->execute($this->data());
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('date', $exception->errors());
        }

        Mail::assertNothingQueued();
    }
}
