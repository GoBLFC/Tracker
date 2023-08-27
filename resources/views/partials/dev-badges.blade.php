<div class="text-center mb-3">
	<span class="badge rounded-pill text-bg-warning">Dev Mode Enabled</span>

	@auth
		<span class="badge rounded-pill text-bg-info">Your Badge ID: {!! Auth::user()->badge_id !!}</span>
		<span class="badge rounded-pill text-bg-info">Your UUID: {!! Auth::user()->id !!}</span>
		<span class="badge rounded-pill text-bg-primary">Role: {!! Auth::user()->role->name !!}</span>
	@endauth

	@kiosk(true)
		<span id="devKioskStatus" class="badge rounded-pill text-bg-success">Kiosk: Authorized</span>
	@else
		<span id="devKioskStatus" class="badge rounded-pill text-bg-danger">Kiosk: Unauthorized</span>
	@endkiosk

	@php($activeEvent = \App\Models\Setting::activeEvent())
	@if($activeEvent)
		<span class="badge rounded-pill text-bg-success">Event: {{ $activeEvent->name }}</span>
	@else
		<span class="badge rounded-pill text-bg-danger">Event: None active</span>
	@endif
</div>
