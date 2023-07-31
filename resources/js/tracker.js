import { postAction, humanDuration, clockDuration } from './shared.js';

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

    $('[data-bs-toggle="tooltip"]').tooltip();

	// If a shift is already ongoing, adjust the total and day time for it and then start the clock
	if(time.ongoingStart) {
		const shiftStart = new Date(time.ongoingStart);
		const shiftTime = Date.now() - shiftStart.getTime();
		time.total -= shiftTime;
		time.day -= shiftTime;
		startShift(shiftStart);
	}
});

function applyLoading(elem, text) {
    const loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> ' + text;

    if ($(elem).html() !== loadingText) {
        $(elem).data('original-text', $(elem).html())
        	.html(loadingText)
			.prop('disabled', true);
    }
}

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
        // onClock = true;

        opposite = "In";
        $dept.prop("disabled", true);
        $checkstatus.html('You are currently checked in.');
        $checkstatus.removeClass("alert-danger").addClass("alert-success");
        $shiftclock.removeClass("d-none");
    } else {
        // shiftTime = 0;
        // updateClock();
        // onClock = false;

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

let shiftInterval = null;

function startShift(start = new Date()) {
	renderTimes(start);
	shiftInterval = setInterval(() => { renderTimes(start); }, 1000);
}

function stopShift(stats) {
	// Stop the clock
	clearInterval(shiftInterval);
	shiftInterval = null;

	// Update the stored total/day times
	time.total = stats.total * 1000;
	time.day = stats.day * 1000;

	// Hide the shift duration and re-render the day/total times to ensure accuracy
	$('#currdurr').hide();
	renderTimes(new Date());
}

async function checkIn() {
	const department = document.getElementById('dept').value;
	try {
		const { entry } = await postAction(checkinPostUrl, { department });
		console.log(entry, new Date(entry.start));
		startShift(new Date(entry.start));
		toggleStatus('Out');
	} catch(err) {
		console.error(err);
		toggleStatus('In', false);
	}
}

async function checkOut() {
	try {
		const { stats } = await postAction(checkoutPostUrl);
		stopShift(stats);
		toggleStatus('In');
	} catch(err) {
		console.error(err);
		toggleStatus('Out', false);
	}
}

function renderTimes(start) {
	const shiftTime = Date.now() - start.getTime();
	$('#durrval').text(clockDuration(shiftTime));
	$('#timetoday').text(humanDuration(time.day + shiftTime));
	$('#earnedtime').text(humanDuration(time.total + shiftTime));
}
