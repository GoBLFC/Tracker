@extends('layouts.master')

@section('content')
	<div class="card mb-3">
		<h4 class="card-header text-bg-warning">Admin Controls</h4>
		<div class="card-body">
			@include('partials.admin-nav')

			@yield('admin-content')

			<a class="btn btn-primary float-end" href="{!! route('tracker.index') !!}" role="button">Back</a>
		</div>
	</div>

	@include('partials.management-nav', ['cardClass' => 'mb-3 mt-4'])
@endsection
