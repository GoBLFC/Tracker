<nav>
	<ul class="row list-unstyled mb-4">
		@foreach($reports as $slug => $class)
			<li class="col">
				@php($selected = isset($reportType) && $reportType === $slug)
				@php($classes = ['btn', 'w-100', 'btn-outline-info' => !$selected, 'btn-info' => $selected])
				@php($hasExtraData = in_array(\App\Reports\Concerns\WithExtraData::class, class_implements($class)))

				@if($hasExtraData)
					<div class="dropdown">
						<button type="button" @class(array_merge($classes, ['dropdown-toggle'])) data-bs-toggle="dropdown" aria-expanded="false" @if($selected) aria-current="true" @endif>
							{{ $class::name() }}
						</button>
						<ul class="dropdown-menu">
							@php($key = $class::extraDataKey())
							@foreach($class::extraDataChoices() as $val => $label)
								@php($active = Request::integer($key, $class::extraDataDefaultValue()) === $val)
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
