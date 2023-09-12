<ul class="row list-unstyled mb-4">
	@foreach($reports as $slug => $class)
		<li class="col">
			@php($selected = isset($reportType) && $reportType === $slug)
			<a @class(['btn', 'w-100', 'btn-outline-info' => !$selected, 'btn-info' => $selected]) href="{!! $event ? route('admin.event.reports.view', [$event->id, $slug]) : route('admin.reports.view', $slug) !!}">
				{{ $class::name() }}
			</a>
		</li>
	@endforeach
</ul>
