function toggleSetting(button, off, on, offLoad, onLoad, setting, method, toggle, callback) {
    const status = $(button).data('status');
    const opposite = !Number(status);

    applyLoading(button, (status === 0) ? offLoad : onLoad + ' ' + setting);

    $(button).data('status', opposite);
    postAction({action: method, status: +opposite}, function (data) {
        const statusTextOpposite = ((+opposite === 0) ? off : on);
        const statusText = ((+opposite === 1) ? off : on);

        $(button).html($(button).data('original-text'));

        if (data.code === 1) {
            $(button).html(statusText + ' ' + setting);
            $(button).toggleClass(toggle);

            toastNotify(setting + ' ' + statusTextOpposite + 'd!');
            if (callback !== null) callback(data);
        }
    });
}

function toggleSite(button) {
    toggleSetting(button, 'Disable', 'Enable', 'Disabling', 'Enabling', 'Site', 'setSiteStatus', 'btn-success btn-danger', function (data) {
        console.log("Site status changed.")
    })
}

function toggleKiosk(button) {
    toggleSetting(button, 'Deauthorize', 'Authorize', 'Deauthorizing', 'Authorizing', 'Kiosk', 'setKioskAuth', 'btn-warning btn-danger', function (data) {
        $.cookie("kiosknonce", data.val);
    })
}