<h1>Appointment booked</h1>

<p>Thank you, {{ $appointment->name }}. A confirmation was sent to {{ $appointment->email }}.</p>

<ul>
    <li>Date: {{ $appointment->date->format('d/m/Y H:i') }}</li>
    <li>Status: {{ $appointment->status->label() }}</li>
</ul>
