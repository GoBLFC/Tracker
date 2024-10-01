@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3">
		<h4 class="card-header">Reports</h4>
		<div class="card-body">
			@include('partials.event-selector', [
				'route' => route('admin.event.reports.view', ['event-id', $reportType]),
				'actionWord' => 'View',
			])

			@include('partials.reports-nav')

			@if($event)
				@php($data = $report->toCollection())
				@if($report instanceof \App\Reports\Concerns\WithExtraParam)
					@php($extraParamValue = request()->integer($report->extraParamKey(), $report->extraParamDefaultValue()))
				@endif

				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">
							{{ $report->name() }}

							@isset($extraParamValue)
								@php($extraLabel = $report->extraParamChoices()[$extraParamValue] ?? '')
								{{ $extraLabel ? "({$extraLabel})" : '' }}
							@endisset
						</h5>

						<div class="btn-group" role="group">
							<button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
								Export
							</button>
							<ul class="dropdown-menu">
								@foreach($exportTypes as $extension => $label)
									@php($routeParams = [$event->id, $reportType, $extension])
									@php($route = route('admin.event.reports.export',
										isset($extraParamValue)
											? array_merge($routeParams, [$report->extraParamKey() => $extraParamValue])
											: $routeParams
									))

									<li><a class="dropdown-item" href="{!! $route !!}">{{ $label }}</a></li>
								@endforeach
							</ul>
						</div>
					</div>

					<div class="card-body">
						@if(isset($data['totals']))
							<dl class="d-flex gap-4 justify-content-center mb-4">
								@php($totalLabel = Str::singular($data['totals'][0]))

								@foreach($data['totals'] as $index => $val)
									@if($index === 0) @continue @endif
									@if($val !== null)
										<div>
											<dt class="d-inline">
												{{ $totalLabel }} {{ $data['head'][$index] }}:
											</dt>
											<dl class="d-inline">
												{{ is_numeric($val) ? round($val, 2) : $val }}
											</dl>
										</div>
									@endif
								@endforeach
							</dl>
						@endif

						<table id="report" class="table table-dark table-striped mb-0">
							@if(isset($data['head']))
								<thead>
									<tr>
										@for($h = 0; $h < count($data['head']); $h++)
											@php($type = isset($data['body'][0]) && (is_numeric($data['body'][0][$h]) || $data['body'][0][$h] instanceof \Carbon\Carbon) ? 'number' : 'html')
											<th scope="col" data-type="{!! $type !!}">
												{!! Str::replace(' ', '&nbsp;', htmlspecialchars($data['head'][$h])) !!}
											</th>
										@endfor
									</tr>
								</thead>
							@endif

							<tbody class="table-group-divider">
								@foreach($data['body'] as $row)
									<tr>
										@foreach($row as $val)
											@php($isDate = $val instanceof \Carbon\Carbon)
											<td @if($isDate) data-order="{!! $val->timestamp !!}" @endif>
												@if($isDate)
													{{ $val->toDayDateTimeString() }}
												@elseif(is_numeric($val))
													{!! round($val, 2) !!}
												@else
													{!! nl2br(htmlspecialchars($val)) !!}
												@endif
											</td>
										@endforeach
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			@else
				<div class="alert alert-info mb-0" role="alert">Please select an event to view reports for.</div>
			@endif
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/legacy/admin/report.js')
@endpush

@push('scripts')
	@if($event)
		<script type="text/javascript">
			const defaultSortColumn = {!! $report->defaultSortColumn() !!};
			const defaultSortDirection = '{!! $report->defaultSortDirection() !!}';
		</script>
	@endif
@endpush
