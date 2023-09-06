<div class="card mb-4">
	<div class="card-header">Edit for Event</div>
	<div class="card-body">
		@if($events)
			<div class="input-group">
				<label for="eventSelector" class="input-group-text">Event</label>
				<select id="eventSelector" class="form-control" data-route="{!! $route !!}">
					@if(!$event) <option value="" selected disabled hidden>Select an Event</option> @endif
					@foreach($events as $otherEvent)
						<option value="{!! $otherEvent->id !!}" {!! $otherEvent->id === $event?->id ? 'selected' : '' !!}>
							{{ $otherEvent->name }}
						</option>
					@endforeach
				</select>
			</div>
		@else
			<p class="mb-0">
				There aren't any events yet - you'll need to <a href="{!! route('admin.events') !!}">create one</a> to manage.
			</p>
		@endif
	</div>
</div>

@push('modules')
	@vite('resources/js/event-selector.js')
@endpush