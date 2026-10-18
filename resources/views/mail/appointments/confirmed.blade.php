<x-mail::message>
# Hello {{ $appointment->name }},

We have received your appointment.

- **Date:** {{ $appointment->date->format('d/m/Y') }}
- **Time:** {{ $appointment->date->format('H:i') }}
- **Status:** {{ $appointment->status->label() }}

If you need to change or cancel it, just reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
