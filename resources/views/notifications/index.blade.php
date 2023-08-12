@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h4 class="card-header">New alerts</h4>
		<div class="card-body">
			@foreach($notifications as $notif)
				<div class="alert alert-{!! $notif['type'] ?? 'dark' !!}" role="alert">
					<h5 class="alert-heading">{{ $notif['title'] }}</h5>
					<p class="mb-0">{!! nl2br(htmlspecialchars($notif['description'])) !!}</p>
				</div>
			@endforeach

			<form action="{!! route('notifications.acknowledge') !!}" method="POST">
				<input type="hidden" name="_token" value="{!! csrf_token() !!}" />
				<button type="submit" class="btn btn-success">Acknowledge</button>
			</form>
		</div>
	</div>
@endsection
