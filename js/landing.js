$(document).ready(function () {
    $('#checkinout').on('click', function () {
        const $this = $(this);

        applyLoading($this, 'Checking ' + $this.data('value') + '...');

        if ($this.data('value') === "in") {
            checkIn(function (data) {
                toggleStatus("Out", data);
            });
        } else {
            checkOut(function (data) {
                toggleStatus("In", data);
            });
        }
    });
    $('[data-bs-toggle="tooltip"]').tooltip();
});

var logoutTime = 60;
let onClock = false;
let shiftTime = 0;

function initData() {
    initClock();
}

function initClock() {
    getClockTime(function (data) {
        if (data.val === -1) return;
        shiftTime = data.val;
        onClock = true;
        $('#currdurr').removeClass("d-none");
        updateClock();
    });

    getMinutesToday(function (data) {
        if (data.val === -1) return;
        $('#hourstoday').html(Math.round((data.val / 60) * 10) / 10);
    });

    getEarnedTime(function (data) {
        if (data.val === -1) return;
        $('#earnedtime').html(Math.round((data.val / 60) * 10) / 10);
    });
}

function applyLoading(elem, text) {
    const loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> ' + text;

    if ($(elem).html() !== loadingText) {
        $(elem).data('original-text', $(elem).html());
        $(elem).html(loadingText);
    }
}

function toggleStatus(status, data) {
    const $dept = $('#dept');
    const $checkstatus = $('#checkstatus');
    const $button = $('#checkinout');
    const $shiftclock = $('#currdurr');

    if (data.code !== 1) {
        $button.html($button.data('original-text'));
        return;
    }

    $button.html('Check-' + status);
    $button.data('value', status.toLowerCase());
    $button.data(status.toLowerCase());
    let opposite = "Out";
    if (status === "Out") {
        onClock = true;

        opposite = "In";
        $dept.prop("disabled", true);
        $checkstatus.html('You are currently checked in.');
        $checkstatus.removeClass("alert-danger").addClass("alert-success");
        $shiftclock.removeClass("d-none");
    } else {
        shiftTime = 0;
        updateClock();
        onClock = false;

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

function clockCycle() {
    setTimeout(function () {
        if (onClock) {
            shiftTime++;
            updateClock();
        }

        clockCycle();
    }, 1000);
}

function updateClock() {
    $("#durrval").text(luxon.Duration.fromObject({seconds: shiftTime}).toFormat("h:mm:ss"));
}

function decrementLogout() {
    if (logoutTime === 0) $('.autologout').html("Goodbye!");
    if (logoutTime === -1) {
        window.location.href = "/logout.php";
        return;
    }

    setTimeout(function () {
        logoutTime--;
        if (logoutTime > 0) $('#lsec').text(logoutTime);
        decrementLogout();
    }, 1000);
}

function checkIn(callback) {
    const dept = $("#dept").children("option:selected").val();
    postAction("/api.php", {action: "checkIn", dept: dept}, function (data) {
        callback(data);
    });
}

function checkOut(callback) {
    postAction("/api.php", {action: "checkOut"}, function (data) {
        if (data.code <= -1) {
            Toast.fire({
                text: data.msg,
                icon: (data.code === -3 ? "error" : "warning")
            });
            if (data.code === -3) {
                $("#audio")[0].volume = 0.35;
                $("#audio")[0].play();
                alert('YOU JUST HAD TO BREAK IT DIDN\'T YOU?');
                location.reload();
            }
        }

        callback(data);
    });
}

function ackAllNotifs(callback) {
    postAction("/api.php", {action: "ackAllNotifs"}, function (data) {
        callback(data);
    });
}

function getClockTime(callback) {
    postAction("/api.php", {action: "getClockTime"}, function (data) {
        callback(data);
    });
}

function getMinutesToday(callback) {
    postAction("/api.php", {action: "getMinutesToday"}, function (data) {
        callback(data);
    });
}

function getEarnedTime(callback) {
    postAction("/api.php", {action: "getEarnedTime"}, function (data) {
        callback(data);
    });
}
