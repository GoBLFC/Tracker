<div class="autologout float-end mb-3">
	<a id="logout" class="btn btn-danger btn-sm" role="button" href="{!! route('auth.logout') !!}">Logout</a>
</div>

@section('scripts')
	@parent

	<script type="text/javascript">
		var logoutUrl = '{!! route('auth.logout') !!}';
		var logoutTime = {!! $devMode ? 3600 : 60 !!};
	</script>

	@vite('resources/js/auto-logout.js')
@endsection
