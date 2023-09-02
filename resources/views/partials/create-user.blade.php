<div class="card {!! $cardClasses ?? '' !!}">
	<{!! $headerTag ?? 'h5' !!} class="card-header">Create User</{!! $headerTag ?? 'h5' !!}>
	<div class="card-body">
		<div class="input-group">
			<input type="text" inputmode="numeric" pattern="[0-9]+" class="form-control" placeholder="Badge Number" aria-label="Badge Number" />
			<button id="createUser" class="btn btn-success" type="button">
				Create User
			</button>
		</div>
	</div>
</div>

@push('modules')
	@vite('resources/js/create-user.js')
@endpush

@push('scripts')
	<script type="text/javascript">
		const userStoreUrl = '{!! route('users.store') !!}';
	</script>
@endpush
