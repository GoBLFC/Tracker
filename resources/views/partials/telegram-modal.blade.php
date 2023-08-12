<div class="modal fade" id="telegramModal" tabindex="-1" aria-labelledby="telegramModalTitle" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="telegramModalTitle">Scan to add bot</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<img
					src="https://chart.googleapis.com/chart?chs=400x400&cht=qr&chld=L|1&chl={!! urlencode(Auth::user()->getTelegramSetupUrl()) !!}"
					width="400"
					height="400"
					class="img-fluid img-thumbnail d-block mx-auto mb-4"
					title="Opens Telegram to add bot"
					alt="QR Code"
					loading="lazy" />

				<p>Scanning the above QR code will give you a URL to add the Telegram bot and link your Telegram profile to your volunteer account automatically.</p>

				<p>This bot can provide you:</p>
				<ul>
					<li>Hours clocked</li>
					<li>Reward list</li>
					<li>Quick login code</li>
				</ul>

				<p>This bot will also:</p>
				<ul>
					<li>Remind you when you're eligible for a reward</li>
					<li>Confirm that you've claimed a reward</li>
					<li>Notify you if you've forgotten to check out for a shift</li>
				</ul>
			</div>
		</div>
	</div>
</div>
