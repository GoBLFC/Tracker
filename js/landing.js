$(document).ready(function () {
    $('#checkinout').on('click', function () {
        const $this = $(this);
        const loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Checking ' + $this.data('value') + '...';

        if ($(this).html() !== loadingText) {
            $this.data('original-text', $(this).html());
            $this.html(loadingText);
        }

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
});

let logoutTime = 600;
let onClock = false;
let shiftTime = 0;

$(document).ready(function () {
    initData();
    clockCycle();
    decrementLogout();
});

function initData() {
    getClockTime(function (data) {
        if (data.val === -1) return;
        shiftTime = data.val;
        onClock = true;
        $('#currdurr').show();
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
        opposite = "In";
        $dept.prop("disabled", true);
        $checkstatus.html('You are currently checked in.');
        $checkstatus.attr('class', 'alert alert-success');
        $shiftclock.show();
    } else {
        $dept.prop("disabled", false);
        $checkstatus.html('You are currently not checked in.');
        $checkstatus.attr('class', 'alert alert-danger');
        $shiftclock.hide();
    }

    $.notify({
        message: 'Checked ' + opposite + '!'
    }, {
        type: 'success',
        delay: 1500,
        animate: {
            enter: 'animated bounceInRight',
            exit: 'animated bounceOutRight'
        },
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
    let measuredTime = new Date(null);
    let cutStart = 12, cutEnd = 7;
    if (shiftTime >= 36000) cutStart = 11, cutEnd = 8;
    measuredTime.setSeconds(shiftTime);
    $('#durrval').html(measuredTime.toISOString().substr(cutStart, cutEnd));
}

function decrementLogout() {
    if (logoutTime === 1) $('#gram').text("second");
    if (logoutTime === 0) $('.autologout').html("Goodbye!");
    if (logoutTime === -1) {
        window.location.href = "/tracker/?logout=timeout";
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
    postAction({action: "checkIn", dept: dept}, function (data) {
        onClock = true;
        callback(data);
    });
}

function checkOut(callback) {
    postAction({action: "checkOut"}, function (data) {
        // Submit times, reset clock
        onClock = false;
        shiftTime = 0;
        updateClock();

        callback(data);
    });
}

function getClockTime(callback) {
    postAction({action: "getClockTime"}, function (data) {
        callback(data);
    });
}

function getMinutesToday(callback) {
    postAction({action: "getMinutesToday"}, function (data) {
        callback(data);
    });
}

function getEarnedTime(callback) {
    postAction({action: "getEarnedTime"}, function (data) {
        callback(data);
    });
}

function postAction(data, callback) {
    $.post("pages/actions.php", data)
        .done(function (data) {
            if (data.code === 0) alert('Error: ' + data.msg);
            callback(data);
            console.log(data);
        }).fail(function (data) {
        alert('Internal error, please contact a staff member for assistance.');
    });
}