@kiosk(true)
	<button type="button" id="toggleKiosk" class="btn btn-sm btn-danger" data-kiosk="true">Deauthorize Kiosk</button>
@else
	<button type="button" id="toggleKiosk" class="btn btn-sm btn-warning" data-kiosk="false">Authorize Kiosk</button>
@endkiosk

@section('scripts')
	@parent

	<script type="text/javascript">
		var kioskAuthorizeUrl = '{!! route('kiosk.authorize.post') !!}';
		var kioskDeauthorizeUrl = '{!! route('kiosk.deauthorize.post') !!}';
	</script>

	@vite('resources/js/toggle-kiosk.js')
@endsection
