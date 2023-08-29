import $ from 'jquery';
import { TempusDominus, Namespace as TDNamespace } from '@eonasdan/tempus-dominus';
import { DateTime } from 'luxon';
import Swal from 'sweetalert2/dist/sweetalert2.js';
import { addRow, debounce, sendGetRequest, sendPostRequest, sendPutRequest, sendDeleteRequest, Toast, initTooltips, humanDuration, prepareDateForInput, isElementInView } from './shared.js';
import { renderTimes, shiftInterval, startShift, stopShift } from './tracker.js';

let timeStart;
let timeStop;
let currentUser = null;
let time = null;

$(() => {
	$('#searchinput').on("input", debounce(function () {
		userSearch($("#searchinput").val())
	}, 250));

	$('#createUser').on('click', function() {
		const val = $(this).parent().find('.form-control').val();
		createUser(val);
	});

	$('#rewards button[data-reward-id]').on('click', function() {
		toggleClaim($(this));
	});

	$('#timeStartInput, #timeStopInput, #dept').on('change', function() {
		$('#addtime').prop('disabled', !isAddTimeReady());
		$('#checkin').prop('disabled', !isCheckinReady());
	});

	$('#checkin').on('click', function() {
		checkIn();
	});

	$('#addtime').on('click', function() {
		addTime();
	});

	$('button[data-user-id]').on('click', async function() {
		loadVolunteer($(this).data('user-id'));
	});

	// Set up time pickers
	timeStart = new TempusDominus(document.getElementById("timeStart"), { restrictions: { maxDate: new Date() } });
	timeStop = new TempusDominus(document.getElementById("timeStop"));

	// Update the minimum datetime of the stop time whenever the start time changes
	timeStart.subscribe(TDNamespace.events.change, evt => {
		timeStop.updateOptions({ restrictions: { minDate: evt.date } });
	});

	// Update the maximum datetime of the start time every 5 seconds
	setInterval(() => {
		timeStart.updateOptions({ restrictions: { maxDate: new Date() } });
	}, 5000);
});

async function userSearch(input) {
	$("#uRow").empty();

	input = input.trim();
	if (input === '') {
		$('#usearchCard').addClass('d-none');
		return;
	}

	const data = await sendGetRequest(userSearchUrl, {q: input});
	$('#usearchCard').removeClass('d-none');

	if(data.users.length > 0) {
		$('#uempty').addClass('d-none');
		$('#utable').removeClass('d-none');
		$.each(data['users'], function (index, user) {
			addUserRow(
				user.id,
				user.badge_id,
				user.username,
				user.badge_name,
				`${user.first_name} ${user.last_name}`,
				user.time_entries?.[0]?.department?.name, user.role === -1
			);
		});
		$('#utable button[data-user-id]').on('click', function() {
			loadVolunteer($(this).data('user-id'));
		});
	} else {
		$('#uempty').removeClass('d-none');
		$('#utable').addClass('d-none');
	}
}

async function loadVolunteer(id) {
	const [timeData, claimData] = await Promise.all([
		sendGetRequest(trackerStatsUrl.replace(/id/, id)),
		sendGetRequest(userClaimsUrl.replace(/id/, id)),
	]);

	$("#eRow").empty();

	const user = timeData.user;
	$("#userCard").removeClass("d-none");
	$("#userCardTitle").text(user.badge_name ?? user.username);
	currentUser = user;

	initClock(timeData.stats, timeData.ongoing);

	$('#rewards button[data-reward-id]')
		.addClass('btn-success')
		.removeClass('btn-danger')
		.text('Claim');

	$.each(claimData.reward_claims, function(key, val) {
		$(`#rewards button[data-reward-id='${val.reward_id}']`)
			.removeClass("btn-success")
			.addClass("btn-danger")
			.data("claim-id", val.id)
			.text("Unclaim");
	});

	// Add time entries
	$.each(timeData.stats.entries, function (index, value) {
		const checkIn = DateTime.fromISO(value.start).setZone(timezone);
		const checkOut = value.stop ? DateTime.fromISO(value.stop).setZone(timezone) : null;
		const worked = (checkOut ?? DateTime.now()).diff(checkIn);
		addEntryRow(
			value.id,
			`${checkIn.toLocaleString({ weekday: 'short' })}, ${checkIn.toLocaleString({ dateStyle: 'medium', timeStyle: 'short' })}`,
			checkOut ? `${checkOut.toLocaleString({ weekday: 'short' })}, ${checkOut.toLocaleString({ dateStyle: 'medium', timeStyle: 'short' })}` : null,
			departments.find(dept => dept.id === value.department_id).name,
			humanDuration(worked),
			humanDuration(worked.plus(value.bonus_time * 1000)),
			value.notes,
			value.auto,
		);
	});

	$('#eNone').toggleClass('d-none', timeData.stats.entries.length > 0);
	$('#eSome').toggleClass('d-none', timeData.stats.entries.length < 1);

	$('#eRow button.delete').on('click', function() {
		removeTime($(this).data('id'));
	});

	$('#eRow button.checkout').on('click', function() {
		checkOut($(this).data('id'));
	});

	initTooltips(document.getElementById('userCard'));

	// Pulse the card border
	const card = document.getElementById('userCard');
	card.classList.remove('transition-border');
	card.classList.replace('border-info', 'border-info-subtle');
	setTimeout(() => {
		card.classList.add('transition-border');
		card.classList.replace('border-info-subtle', 'border-info');
		setTimeout(() => {
			card.classList.replace('border-info', 'border-info-subtle');
		}, 500);
	}, 0);

	// Scroll to the card if the title isn't in view
	if(!isElementInView(document.getElementById('userCardTitle'))) {
		card.scrollIntoView({ block: card.clientHeight < window.innerHeight ? 'center' : 'start' });
	}
}

function initClock(stats, ongoing) {
	if(shiftInterval) {
		stopShift(
			{
				total: 0,
				day: 0,
				ongoingStart: 0
			},
			{
				total: 0,
				day: 0
			},
		);
	}

	time = {
		total: stats.total * 1000,
		day: stats.day * 1000,
		ongoingStart: ongoing ? DateTime.fromISO(ongoing.start).toMillis() : null,
	};

	$('#currdurr').toggleClass("d-none", !ongoing);

	if(!ongoing) {
		renderTimes(time, new Date());
		return;
	}

	const shiftStart = new Date(time.ongoingStart);
	const shiftTime = Date.now() - shiftStart.getTime();
	time.total -= shiftTime;
	time.day -= shiftTime;
	startShift(time, shiftStart);
}

async function createUser(badge_id) {
	const data = await sendPostRequest(userCreatePostUrl, { badge_id });
	Toast.fire({
		text: `User (${data.user.badge_id}) created.`,
		icon: "success"
	});
}

async function checkIn() {
	const department_id = $("#dept").val();
	const notes = $("#notes").val();
	const start = timeStart.dates.picked[0] ? prepareDateForInput(timeStart.dates.picked[0], timezone) : null;

	await sendPutRequest(timePutUrl.replace(/id/, currentUser.id), { department_id, start, notes });

	loadVolunteer(currentUser.id);
	Toast.fire({
		text: "User checked in.",
		icon: "success"
	});
}

async function addTime() {
	const start = timeStart.dates.picked[0] ? prepareDateForInput(timeStart.dates.picked[0], timezone) : null;
	const stop = timeStop.dates.picked[0] ? prepareDateForInput(timeStop.dates.picked[0], timezone) : null;
	const department_id = $("#dept").val();
	const notes = $("#notes").val();

	await sendPutRequest(timePutUrl.replace(/id/, currentUser.id), {
		department_id,
		start,
		stop,
		notes,
	});

	loadVolunteer(currentUser.id);
	Toast.fire({
		text: !stop ? 'User checked in.' : 'Added time entry.',
		icon: "success"
	});
}

async function removeTime(id) {
	const result = await Swal.fire({
		title: 'Delete time entry?',
		icon: 'warning',
		showCancelButton: true,
		focusCancel: true,
		confirmButtonText: 'Delete',
	});
	if(!result.isConfirmed) return;

	await sendDeleteRequest(timeDeleteUrl.replace(/id/, id));
	loadVolunteer(currentUser.id);
	Toast.fire({
		text: "Removed time entry.",
		icon: "success"
	});
}

async function checkOut(id) {
	await sendPostRequest(timeCheckoutPostUrl.replace(/id/, id));
	loadVolunteer(currentUser.id);
	Toast.fire({
		text: "User checked out.",
		icon: "success"
	});
}

async function toggleClaim(button) {
	// Lazyness
	const claimId = button.data('claim-id');
	if (claimId) {
		const rewardId = button.data('reward-id');
		const reward = rewards.find(reward => reward.id === rewardId);
		const result = await Swal.fire({
			title: 'Unclaim reward?',
			text: `${reward.hours}hr reward: ${reward.name}`,
			icon: 'warning',
			showCancelButton: true,
			focusCancel: true,
			confirmButtonText: 'Unclaim',
		});
		if(!result.isConfirmed) return;

		await sendDeleteRequest(userClaimsDeleteUrl.replace(/id/, claimId));
		button.removeClass("btn-danger")
			.addClass("btn-success")
			.data('claim-id', null)
			.text("Claim");
	} else {
		const reward_id = button.data('reward-id');
		const { reward_claim: claim } = await sendPutRequest(userClaimsPutUrl.replace(/id/, currentUser.id), { reward_id });
		button.removeClass("btn-success")
			.addClass("btn-danger")
			.data("claim-id", claim.id)
			.text("Unclaim");
	}
}

function isAddTimeReady() {
	return $('#timeStartInput').val() && $('#timeStopInput').val() && $('#dept').val();
}

function isCheckinReady() {
	return !$('#timeStopInput').val() && $('#dept').val();
}

function addUserRow(uuid, id, username, badgeName, name, dept, banned) {
	let status = "<span class=\"badge rounded-pill text-bg-" + (!dept ? "warning" : "success") + "\">" + (!dept ? "Checked Out" : `Checked In: ${dept}`) + "</span>";
	if (banned) status = status.concat(" <span class=\"badge rounded-pill text-bg-danger\">Banned</span>");
	const data = [id, username, badgeName ?? '', name, status, "<button type=\"button\" class=\"btn btn-sm btn-info\" data-user-id=\"" + uuid + "\">Load</button>"];
	addRow(true, $("#uRow"), data)
}

function addEntryRow(id, checkin, checkout, dept, worked, earned, notes, auto) {
	let notesPill = notes ? `<span data-bs-toggle="tooltip" title="${notes.replace(/"/g, '\\"')}" class="badge rounded-pill text-bg-info info-badge">Notes</span>` : '';
	if (auto) notesPill += ` <span data-bs-toggle="tooltip" title="This entry was automatically closed at the end of the day." class="badge rounded-pill text-bg-warning info-badge">Auto</span>`;
	const statusButton = !checkout ? `<button type="button" class="btn btn-sm btn-warning checkout" data-id="${id}">Checkout</button>` : '';
	const actions = `<div class="btn-group float-end" role="group" aria-label="Time entry actions">
		${statusButton}
		<button type="button" class="btn btn-sm btn-danger delete" data-id="${id}">Delete</button>
	</div>`;
	const data = [checkin, !checkout ? "Ongoing..." : checkout, dept, worked, earned, notesPill, actions];
	addRow(false, $("#eRow"), data)
}
