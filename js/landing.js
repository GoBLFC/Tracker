$(document).ready(function () {
    $('#checkin').on('click', function () {
        var $this = $(this);
        var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Checking in...';
        if ($(this).html() !== loadingText) {
            $this.data('original-text', $(this).html());
            $this.html(loadingText);
        }

        checkIn(function () {
            $this.html($this.data('original-text'));
        });
    });
})

let logoutTime = 600;

$(document).ready(function () {
    decrementLogout();
})

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
        callback();
        if (data.code === 0) {
            alert('Error while checking in: ' + data.msg);
        }
    });
}

function postAction(data, callback) {
    $.post("pages/actions.php", data)
        .done(function (data) {
            console.log(data);
            callback(data);
        });
}