<ul class="row list-unstyled mb-4">
	@foreach($reports as $slug => $class)
		<li class="col">
			@php($selected = isset($reportType) && $reportType === $slug)
			@php($classes = ['btn', 'w-100', 'btn-outline-info' => !$selected, 'btn-info' => $selected])
			@php($hasExtraData = in_array(\App\Reports\Concerns\WithExtraData::class, class_implements($class)))

			@if($hasExtraData)
				<div class="dropdown">
					<button type="button" @class(array_merge($classes, ['dropdown-toggle'])) data-bs-toggle="dropdown" aria-expanded="false">
						{{ $class::name() }}
					</button>
					<ul class="dropdown-menu">
						@php($key = $class::extraDataKey())
						@foreach($class::extraDataChoices() as $val => $label)
							<li>
								<a href="{!! $event ? route('admin.event.reports.view', [$event->id, $slug, $key => $val]) : route('admin.reports.view', [$slug, $key => $val]) !!}" class="dropdown-item">
									{{ $label }}
								</a>
							</li>
						@endforeach
					</ul>
				</div>
			@else
				<a @class($classes) href="{!! $event ? route('admin.event.reports.view', [$event->id, $slug]) : route('admin.reports.view', $slug) !!}">
					{{ $class::name() }}
				</a>
			@endif
		</li>
	@endforeach
</ul>
