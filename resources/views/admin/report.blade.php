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

				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">
							{{ $report->name() }}
							@if($report instanceof \App\Reports\Concerns\WithExtraParam)
								@php($extraLabel = $report->extraParamChoices()[request()->integer($report->extraParamKey(), $report->extraParamDefaultValue())] ?? '')
								{{ $extraLabel ? "({$extraLabel})" : '' }}
							@endif
						</h5>

						<div class="btn-group" role="group">
							<button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
								Export
							</button>
							<ul class="dropdown-menu">
								@foreach($exportTypes as $extension => $label)
									<li><a class="dropdown-item" href="{!! route('admin.event.reports.export', [$event->id, $reportType, $extension]) !!}">{{ $label }}</a></li>
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
	@vite('resources/js/admin/report.js')
@endpush

@push('scripts')
	@if($event)
		<script type="text/javascript">
			const defaultSortColumn = {!! $report->defaultSortColumn() !!};
			const defaultSortDirection = '{!! $report->defaultSortDirection() !!}';
		</script>
	@endif
@endpush
