@kiosk(true)
	<button type="button" id="toggleKiosk" class="{!! $kioskToggleClasses ?? 'btn btn-sm' !!} btn-danger" data-kiosk="true">Deauthorize Kiosk</button>
@else
	<button type="button" id="toggleKiosk" class="{!! $kioskToggleClasses ?? 'btn btn-sm' !!} btn-warning " data-kiosk="false">Authorize Kiosk</button>
@endkiosk

@push('modules')
	@vite('resources/js/legacy/toggle-kiosk.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const kioskAuthorizePostUrl = '{!! route('kiosks.authorize.post') !!}';
		const kioskDeauthorizePostUrl = '{!! route('kiosks.deauthorize.post') !!}';
	</script>
@endpush
