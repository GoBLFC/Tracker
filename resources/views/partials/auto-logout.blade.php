<div class="autologout float-end mb-3">
	<a id="logout" class="btn btn-danger btn-sm" role="button" href="{!! route('auth.logout') !!}">Logout</a>
</div>

@push('modules')
	@vite('resources/js/auto-logout.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const logoutUrl = '{!! route('auth.logout') !!}';
		const logoutTime = @devMode 3600 @else 60 @enddevMode;
	</script>
@endpush
