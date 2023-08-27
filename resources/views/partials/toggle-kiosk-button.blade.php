@kiosk(true)
	<button type="button" id="toggleKiosk" class="{!! $kioskToggleClasses ?? 'btn btn-sm' !!} btn-danger" data-kiosk="true">Deauthorize Kiosk</button>
@else
	<button type="button" id="toggleKiosk" class="{!! $kioskToggleClasses ?? 'btn btn-sm' !!} btn-warning " data-kiosk="false">Authorize Kiosk</button>
@endkiosk

@push('modules')
	@vite('resources/js/toggle-kiosk.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const kioskAuthorizePostUrl = '{!! route('kiosk.authorize.post') !!}';
		const kioskDeauthorizePostUrl = '{!! route('kiosk.deauthorize.post') !!}';
	</script>
@endpush
