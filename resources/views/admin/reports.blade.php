@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Reports</h4>
		<div class="card-body">
			@include('partials.event-selector', [
				'route' => route('admin.event.reports', ['event-id']),
				'actionWord' => 'View',
			])

			@if($event)
				@include('partials.reports-nav')

				<div class="alert alert-info mb-0" role="alert">Please select a report to view.</div>
			@else
				<div class="alert alert-info mb-0" role="alert">Please select an event to view reports for.</div>
			@endif
		</div>
	</div>
@endsection
