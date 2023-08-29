import $ from 'jquery';
import Swal from 'sweetalert2/dist/sweetalert2.js';
import { sendPostRequest, humanDuration, clockDuration, applyLoading, initTooltips } from './shared.js';

$(() => {
    $('#checkinout').on('click', function () {
        const $this = $(this);

        applyLoading($this, 'Checking ' + $this.data('value') + '...');

        if ($this.data('value') === 'in') {
            checkIn();
        } else {
            checkOut();
        }
    });

	$('#dept').on('change', function() {
		$('#checkinout').prop('disabled', !$(this).val());
	});

    initTooltips();

	// If a shift is already ongoing, adjust the total and day time for it and then start the clock
	if(typeof time !== 'undefined' && time.ongoingStart) {
		const shiftStart = new Date(time.ongoingStart);
		const shiftTime = Date.now() - shiftStart.getTime();
		time.total -= shiftTime;
		time.day -= shiftTime;
		startShift(time, shiftStart);
	}
});

function toggleStatus(status, success = true) {
    const $dept = $('#dept');
    const $checkstatus = $('#checkstatus');
    const $button = $('#checkinout');
    const $shiftclock = $('#currdurr');

    if (!success) {
        $button.html($button.data('original-text')).prop('disabled', false);
        return;
    }

    $button.html('Check-' + status)
    	.data('value', status.toLowerCase())
		.prop('disabled', false);

    let opposite = "Out";
    if (status === "Out") {
        opposite = "In";
        $dept.prop("disabled", true);
        $checkstatus.html('You are currently checked in.');
        $checkstatus.removeClass("alert-danger").addClass("alert-success");
        $shiftclock.removeClass("d-none");
    } else {
        $dept.prop("disabled", false);
        $checkstatus.html('You are currently not checked in.');
        $checkstatus.removeClass("alert-success").addClass("alert-danger");
        $shiftclock.addClass("d-none");
    }

    Swal.fire({
        text: "Checked " + opposite,
        icon: "success",
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        timer: 2000
    });
}

export let shiftInterval = null;

export function startShift(time, start = new Date()) {
	renderTimes(time, start);
	shiftInterval = setInterval(() => { renderTimes(time, start); }, 1000);
}

export function stopShift(time, stats) {
	// Stop the clock
	clearInterval(shiftInterval);
	shiftInterval = null;

	// Update the stored total/day times
	time.total = stats.total * 1000;
	time.day = stats.day * 1000;

	// Hide the shift duration and re-render the day/total times to ensure accuracy
	$('#currdurr').addClass('d-none');
	renderTimes(time, new Date());
}

export async function checkIn() {
	const department_id = document.getElementById('dept').value;
	try {
		const { time_entry: entry } = await sendPostRequest(checkinPostUrl, { department_id });
		startShift(time, new Date(entry.start));
		toggleStatus('Out');
	} catch(err) {
		console.error(err);
		toggleStatus('In', false);
	}
}

export async function checkOut() {
	try {
		const { stats } = await sendPostRequest(checkoutPostUrl);
		stopShift(time, stats);
		toggleStatus('In');
	} catch(err) {
		console.error(err);
		toggleStatus('Out', false);
	}
}

export function renderTimes(time, start) {
	const shiftTime = Date.now() - start.getTime();
	$('#durrval').text(clockDuration(shiftTime));
	$('#timetoday').text(humanDuration(time.day + shiftTime));
	$('#earnedtime').text(humanDuration(time.total + shiftTime));
}
