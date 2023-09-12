<nav>
	<ul class="list-unstyled row g-2 g-md-4 mb-4">
		@foreach($reports as $slug => $class)
			<li class="col">
				@php($selected = isset($reportType) && $reportType === $slug)
				@php($classes = ['btn', 'w-100', 'h-100', 'btn-outline-info' => !$selected, 'btn-info' => $selected])
				@php($hasExtraParam = in_array(\App\Reports\Concerns\WithExtraParam::class, class_implements($class)))

				@if($hasExtraParam)
					<div class="dropdown h-100">
						<button type="button" @class(array_merge($classes, ['dropdown-toggle'])) data-bs-toggle="dropdown" aria-expanded="false" @if($selected) aria-current="true" @endif>
							{{ $class::name() }}
						</button>
						<ul class="dropdown-menu">
							@php($key = $class::extraParamKey())
							@foreach($class::extraParamChoices() as $val => $label)
								@php($active = $selected && Request::integer($key, $class::extraParamDefaultValue()) === $val)
								<li>
									<a @class(['dropdown-item', 'active' => $active])
										href="{!! $event ? route('admin.event.reports.view', [$event->id, $slug, $key => $val]) : route('admin.reports.view', [$slug, $key => $val]) !!}"
										@if($active) aria-current="true" @endif>

										{{ $label }}
									</a>
								</li>
							@endforeach
						</ul>
					</div>
				@else
					<a @class($classes)
						href="{!! $event ? route('admin.event.reports.view', [$event->id, $slug]) : route('admin.reports.view', $slug) !!}"
						@if($selected) aria-current="true" @endif>

						{{ $class::name() }}
					</a>
				@endif
			</li>
		@endforeach
	</ul>
</nav>
