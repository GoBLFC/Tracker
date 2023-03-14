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
});

var logoutTime = 60;
let onClock = false;
let shiftTime = 0;

function initData() {
    initClock();

    getNotifications(function (data) {
        if (data.val === -1) return;
        let time = 0;
        $.each(data['val'], function (index, n) {
            setTimeout(function () {
                toastNotify(n['message'], n['type'], 30000);
            }, time);
            time += 500;
            postAction("/api.php", {action: "readNotification", id: n['id']});
        });
    });
}

function initClock() {
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
        $checkstatus.attr('class', 'alert alert-success');
        $shiftclock.show();
    } else {
        shiftTime = 0;
        updateClock();
        onClock = false;

        $dept.prop("disabled", false);
        $checkstatus.html('You are currently not checked in.');
        $checkstatus.attr('class', 'alert alert-danger');
        $shiftclock.hide();
    }

    toastNotify('Checked ' + opposite + '!', 'success', 1500)
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
    $('#durrval').html(moment.duration(shiftTime, "seconds").format("h:mm:ss"));
}

function decrementLogout() {
    if (logoutTime === 1) $('#gram').text("second");
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
            toastNotify(data.msg, (data.code === -3 ? "danger" : "warning"), 1500);
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

function getNotifications(callback) {
    postAction("/api.php", {action: "getNotifications"}, function (data) {
        callback(data);
    });
}