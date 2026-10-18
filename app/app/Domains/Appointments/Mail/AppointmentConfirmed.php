<?php

namespace App\app\Domains\Appointments\Mail;

use App\app\Domains\Appointments\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        // Only dispatch after the DB transaction that created the appointment commits.
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your appointment on ' . $this->appointment->date->format('d/m/Y H:i'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.appointments.confirmed',
        );
    }
}
