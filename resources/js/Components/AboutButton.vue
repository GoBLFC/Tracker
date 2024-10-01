<template>
	<div>
		<button
			class="btn btn-sm btn-light"
			data-bs-toggle="modal"
			data-bs-target="#aboutModal"
		>
			About
		</button>

		<Teleport to="body">
			<div
				class="modal fade"
				id="aboutModal"
				tabindex="-1"
				aria-labelledby="aboutModalLabel"
				aria-hidden="true"
			>
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h1 class="modal-title fs-5" id="aboutModalLabel">
								About Tracker
							</h1>
							<button
								type="button"
								class="btn-close"
								data-bs-dismiss="modal"
								aria-label="Close"
							></button>
						</div>

						<div class="modal-body">
							<div class="container-fluid">
								<div class="row mt-4">
									<div class="col-md-4 text-center">
										<img
											src="../../img/authors/ghost.webp"
											class="img-fluid rounded mb-3 w-50 w-md-75"
											alt="Ghost Cabbit's avatar"
										/>
										<h4 class="mb-1">Ghost Cabbit</h4>
										<p class="mb-0">Original Developer</p>
										<p class="fs-2">
											<a
												target="_blank"
												href="https://twitter.com/GhostCabbit"
												title="Ghost's Twitter"
												><i
													class="fa-brands fa-twitter"
												></i
											></a>
										</p>
									</div>
									<div class="col-md-4 text-center">
										<img
											src="../../img/authors/glitch.webp"
											class="img-fluid rounded mb-3 w-50 w-md-75"
											alt="Glitch's avatar"
										/>
										<h4 class="mb-1">Glitch</h4>
										<p class="mb-0">
											Developer / Maintainer
										</p>
										<p class="fs-2">
											<a
												target="_blank"
												href="https://twitter.com/glitchfur"
												title="Glitch's Twitter"
												><i
													class="fa-brands fa-twitter"
												></i
											></a>
											<a
												target="_blank"
												href="https://meow.social/@glitch"
												title="Glitch's Mastodon"
												><i
													class="fa-brands fa-mastodon"
												></i
											></a>
											<a
												target="_blank"
												href="https://www.glitchfur.net"
												title="Glitch's Website"
												><i
													class="fa-solid fa-globe"
												></i
											></a>
										</p>
									</div>
									<div class="col-md-4 text-center">
										<img
											src="../../img/authors/gawdl3y.webp"
											class="img-fluid rounded mb-3 w-50 w-md-75"
											alt="Gawdl3y's avatar"
										/>
										<h4 class="mb-1">Gawdl3y</h4>
										<p class="mb-0">
											Developer / Maintainer
										</p>
										<p class="fs-2">
											<a
												target="_blank"
												href="https://furry.engineer/Gawdl3y"
												title="Gawdl3y's Mastodon"
												><i
													class="fa-brands fa-mastodon"
												></i
											></a>
											<a
												target="_blank"
												href="https://gawdl3y.dev"
												title="Gawdl3y's Website"
												><i
													class="fa-solid fa-globe"
												></i
											></a>
										</p>
									</div>
								</div>

								<hr class="mt-0" />

								<div class="row">
									<div class="col">
										<p>
											Tracker is an online system for
											tracking the amount of time
											volunteers have contributed towards
											conventions. It was originally
											designed for
											<a
												href="https://goblfc.org"
												target="_blank"
												>BLFC</a
											>
											by Ghost Cabbit, with Glitch and
											Gawdl3y making more recent
											developments.
										</p>
										<p>
											If you find bugs, please let your
											volunteer managers know so we can
											fix them!
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</Teleport>
	</div>
</template>
