@extends('layouts.admin')

@section('admin-content')
	<div class="card mb-3" data-section="Site">
		<h4 class="card-header">Site Settings</h4>
		<div class="card-body">
			<dl class="row">
				{{-- Active event selector --}}
				<dt class="col-xl-4 col-md-3 pt-2">
					<form action="{!! route('setting.put', 'active-event') !!}" method="POST" class="seamless">
						@method('put')
						@csrf

						<div class="input-group float-md-end">
							<label class="input-group-text" for="activeEventValue">Active Event</label>
							<select name="value" class="form-select" id="activeEventValue">
								<option value="" {!! !$activeEvent ? 'selected' : '' !!}>None</option>
								@foreach($events as $event)
									<option value="{!! $event->id !!}" {!! $event->id === $activeEvent?->id ? 'selected' : '' !!}>{{ $event->name }}</option>
								@endforeach
							</select>
							<button type="submit" class="btn btn-primary" data-success="Updated the active event.">Save</button>
						</div>
					</form>
				</dt>
				<dd class="col-xl-6 col-md-9 pt-2">
					<p>
						The active event is the event that all volunteers and managers will be entering/managing time for.
						When there is no active event, volunteers won't be able to check in or out, and managers won't be able to view or edit any time entries.
					</p>
				</dd>

				{{-- Dev mode toggle --}}
				<dt class="col-xl-4 col-md-3 pt-4 pb-2">
					<form action="{!! route('setting.put', 'dev-mode') !!}" method="POST" class="seamless">
						@method('put')
						@csrf

						<input type="hidden" name="value" value="@devMode 0 @else 1 @enddevMode" />
						<button type="submit" class="btn float-md-end @devMode btn-success @else btn-danger @enddevMode"
							data-state="@devMode true @else false @enddevMode"
							data-state-input="value"
							data-class-false="btn-danger"
							data-class-true="btn-success"
							data-label-false="Enable Dev Mode"
							data-label-true="Disable Dev Mode"
							data-label-loading-false="Disabling Dev Mode..."
							data-label-loading-true="Enabling Dev Mode..."
							data-success-false="Disabled dev mode."
							data-success-true="Enabled dev mode.">

							@devMode Disable @else Enable @enddevMode Dev Mode
						</button>
					</form>
				</dt>
				<dd class="col-xl-6 col-md-9 pt-4 pb-2">
					<p>
						Dev Mode makes it easier to develop and test Tracker by relaxing the kiosk authorization requirement, greatly extending the auto-logout timer, and disabling logging out of ConCat in parallel with Tracker.<br />
						<strong>If Tracker is running in a production environment, this should be disabled.</strong>
					</p>
				</dd>

				{{-- Lockdown toggle --}}
				<dt class="col-xl-4 col-md-3 py-2">
					<form action="{!! route('setting.put', 'lockdown') !!}" method="POST" class="seamless">
						@method('put')
						@csrf

						<input type="hidden" name="value" value="@lockdown 0 @else 1 @endlockdown" />
						<button type="submit" class="btn float-md-end @lockdown btn-success @else btn-danger @endlockdown"
							data-state="@lockdown true @else false @endlockdown"
							data-state-input="value"
							data-class-false="btn-danger"
							data-class-true="btn-success"
							data-label-false="Enable Lockdown"
							data-label-true="Disable Lockdown"
							data-label-loading-false="Disabling lockdown..."
							data-label-loading-true="Enabling lockdown..."
							data-success-false="Unlocked the site."
							data-success-true="Locked the site down.">

							@lockdown Disable @else Enable @endlockdown Lockdown
						</button>
					</form>
				</dt>
				<dd class="col-xl-6 col-md-9 py-2">
					<p>
						Locking the site down makes it inaccessible to volunteers, prohibiting them from checking in or out.
						Managers and administrators can still log in and perform staff functions, including checking users in or out on their behalf.
					</p>
				</dd>

				{{-- Kiosk toggle --}}
				<dt class="col-xl-4 col-md-3 pt-2">
					@include('partials.toggle-kiosk-button', ['kioskToggleClasses' => 'btn float-md-end'])
				</dt>
				<dd class="col-xl-6 col-md-9 pt-2">
					<p>
						Authorizing this device as a kiosk will allow volunteers to check in or out on this device.
						This is required when setting up dedicated devices pre-con for checking in or out.
						Kiosks remain authorized for {!! Carbon\CarbonInterval::minutes(config('tracker.kiosk_lifetime'))->cascade()->forHumans() !!}.
					</p>
				</dd>
			</dl>
		</div>
	</div>
@endsection

@push('modules')
	@vite('resources/js/seamless-forms.js')
@endpush
