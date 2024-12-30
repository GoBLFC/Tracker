<template>
	<div class="grow max-w-[48rem] mx-auto flex flex-col justify-center gap-4">
		<Head title="About" />

		<img
			src="@/../img/event-logo.png"
			width="128"
			height="146"
			class="object-scale-down mx-auto md:mb-8"
			:alt="appName"
		/>

		<h1 class="text-3xl font-light">About Tracker</h1>

		<p>
			Tracker is an online system for tracking the amount of time
			volunteers have contributed towards conventions and ensuring they
			are appropriately rewarded. It was originally designed for
			<a href="https://goblfc.org" target="_blank">BLFC</a> by Ghost
			Cabbit, with development being continued primarily by the BLFC
			Website team.
		</p>

		<p>
			Tracker is an open-source project, so all are welcome to use it and
			contribute to it! Check out the source code on
			<a href="https://github.com/GoBLFC/Tracker" target="_blank"
				><FontAwesomeIcon :icon="faGithub" /> GitHub</a
			>. If you find any issues, please report them to your Volunteer
			Managers or open an issue on the repository.
		</p>

		<h2 class="text-2xl font-light">Developers</h2>

		<div class="flex flex-col md:flex-row gap-8 md:gap-4">
			<div class="text-center">
				<img
					src="@/../img/authors/ghost.webp"
					class="w-1/2 md:w-3/4 mx-auto mb-3 rounded-md object-scale-down"
					alt="Ghost's avatar"
				/>
				<h3 class="text-xl">Ghost Cabbit</h3>
				<p class="mb-2 text-muted-color">Original Creator</p>
				<ul class="flex justify-center gap-3 text-3xl">
					<li>
						<a
							href="https://twitter.com/GhostCabbit"
							title="Ghost's Twitter"
							target="_blank"
							><FontAwesomeIcon :icon="faTwitter"
						/></a>
					</li>
				</ul>
			</div>

			<div class="text-center">
				<img
					src="@/../img/authors/glitch.webp"
					class="w-1/2 md:w-3/4 mx-auto mb-3 rounded-md object-scale-down"
					alt="Glitch's avatar"
				/>
				<h3 class="text-xl">Glitch</h3>
				<p class="mb-2 text-muted-color">BLFC Website Team Lead</p>
				<ul class="flex justify-center gap-3 text-3xl">
					<li>
						<a
							href="https://bsky.app/profile/glitchfur.net"
							title="Glitch's Bluesky"
							target="_blank"
							><FontAwesomeIcon :icon="faBluesky"
						/></a>
					</li>
					<li>
						<a
							href="https://meow.social/@glitch"
							title="Glitch's Mastodon"
							target="_blank"
							><FontAwesomeIcon :icon="faMastodon"
						/></a>
					</li>
					<li>
						<a
							href="https://www.glitchfur.net"
							title="Glitch's Website"
							target="_blank"
							><FontAwesomeIcon :icon="faGlobe"
						/></a>
					</li>
				</ul>
			</div>

			<div class="text-center">
				<img
					src="@/../img/authors/gawdl3y.webp"
					class="w-1/2 md:w-3/4 mx-auto mb-3 rounded-md object-scale-down"
					alt="Gawdl3y's avatar"
				/>
				<h3 class="text-xl">Gawdl3y</h3>
				<p class="mb-2 text-muted-color">BLFC Website Team</p>
				<ul class="flex justify-center gap-3 text-3xl">
					<li>
						<a
							href="https://bsky.app/profile/gawdl3y.dev"
							title="Gawdl3y's Bluesky"
							target="_blank"
							><FontAwesomeIcon :icon="faBluesky"
						/></a>
					</li>
					<li>
						<a
							href="https://furry.engineer/Gawdl3y"
							title="Gawdl3y's Mastodon"
							target="_blank"
							><FontAwesomeIcon :icon="faMastodon"
						/></a>
					</li>
					<li>
						<a
							href="https://gawdl3y.dev"
							title="Gawdl3y's Website"
							target="_blank"
							><FontAwesomeIcon :icon="faGlobe"
						/></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faGlobe } from '@fortawesome/free-solid-svg-icons';
import { faGithub, faBluesky, faMastodon, faTwitter } from '@fortawesome/free-brands-svg-icons';
</script>
