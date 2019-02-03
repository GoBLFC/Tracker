$(document).ready(function () {
    $('#checkinout').on('click', function () {
        var $this = $(this);
        var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Checking ' + $this.data('value') + '...';

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

$(document).ready(function () {
    decrementLogout();
});

function toggleStatus(status, data) {
    const $dept = $('#dept');
    const $checkstatus = $('#checkstatus');
    const $button = $('#checkinout');

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
    } else {
        $dept.prop("disabled", false);
        $checkstatus.html('You are currently not checked in.');
        $checkstatus.attr('class', 'alert alert-danger');
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
    postAction({action: "checkIn", dept: dept}, callback);
}

function checkOut(callback) {
    postAction({action: "checkOut"}, callback);
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