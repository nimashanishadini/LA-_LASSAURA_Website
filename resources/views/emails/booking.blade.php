<h1>La Lassaura</h1>
<p>Hello {{ $booking->guest_name }},</p>
<p>Booking update: {{ str_replace('_',' ',$kind) }}.</p>
<p>Reference: {{ $booking->reference }}<br>Room: {{ $booking->villa->name }}<br>Stay: {{ $booking->check_in->toDateString() }} to {{ $booking->check_out->toDateString() }}</p>
<p><a href="{{ route('bookings.show',$booking) }}">Sign in to view the current booking status and agreed price</a>.</p>
<p>Pending requests do not reserve dates. Only a confirmed booking reserves a room.</p>
