var logoutTime = 240;

function initData() {
    postAction({action: 'getAdmins'}, function (data) {
        $.each(data['val'], function (index, value) {
            addUserRow("Admin", value['id'], value['nickname']);
        });
    });

    postAction({action: 'getManagers'}, function (data) {
        $.each(data['val'], function (index, value) {
            addUserRow("Manager", value['id'], value['nickname']);
        });
    });

    postAction({action: 'getBanned'}, function (data) {
        $.each(data['val'], function (index, value) {
            addUserRow("Banned", value['id'], value['nickname']);
        });
    });

    postAction({action: 'getDepts'}, function (data) {
        $.each(data['val'], function (index, value) {
            addDeptRow(value['id'], value['name'], value['hidden']);
            $('#depts').append($("<option></option>").attr("value", value['id']).text(value['name']));
        });

        $("#depts").selectpicker("refresh");

        addListeners();
    });

    postAction({action: 'getBonuses'}, function (data) {
        $.each(data['val'], function (index, value) {
            addBonusRow(value['id'], value['start'], value['stop'], value['dept'], value['modifier'] + "x");
        });
    });
}

function addListeners() {
    $(".form-control").off();
    $(".custom-select").off();

    // Detect input focus change
    $(".form-control").focusout(function (e) {
        const elem = $(e.currentTarget);
        if (elem.data("type") === "dept") updateDept(getTableKey(elem.parent()), elem.val(), getButtonSelect(elem));
    });

    // Detect selection change
    $(".custom-select").change(function (e) {
        const elem = $(e.currentTarget);
        if (elem.data("type") === "dept") updateDept(getTableKey(elem.parent()), getButtonInput(elem), getButtonSelect(elem));
    });
}

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

            toastNotify(setting + ' ' + statusTextOpposite + 'd!', 'success', 1500);
            if (callback !== null) callback(data);
        }
    });
}

function setAdmin(value, badgeid, callback) {
    postAction({action: 'setAdmin', badgeid: badgeid, value: value}, function (data) {
        if (data['code'] === 0) return;
        if (value === 0) {
            toastNotify('Admin removed.', 'success', 1500);
        } else {
            toastNotify('Made ' + data['name'] + ' admin!', 'success', 1500);
            addUserRow("Admin", badgeid, data['name']);
        }
        if (callback) callback();
    });
}

function setManager(value, badgeid, callback) {
    postAction({action: 'setManager', badgeid: badgeid, value: value}, function (data) {
        if (data['code'] === 0) return;
        if (value === 0) {
            toastNotify('Manager removed.', 'success', 1500);
        } else {
            toastNotify('Made ' + data['name'] + ' manager!', 'success', 1500);
            addUserRow("Manager", badgeid, data['name']);
        }
        if (callback) callback();
    });
}

function setBanned(value, badgeid, callback) {
    if (value === "") return;
    postAction({action: 'setBanned', badgeid: badgeid, value: value}, function (data) {
        if (data['code'] === 0) return;
        if (value === 0) {
            toastNotify('User (' + data['name'] + ') unbanned.', 'success', 1500);
        } else {
            toastNotify('User (' + data['name'] + ') banned.', 'success', 1500);
            addUserRow("Banned", badgeid, data['name']);
        }
        if (callback) callback();
    });
}

function addDept(elem) {
    const name = getButtonInput(elem);
    const hidden = parseInt(getButtonSelect(elem));
    postAction({action: 'addDept', name: name, hidden: hidden}, function (data) {
        if (data['code'] === 0) return;
        toastNotify('Department created.', 'success', 1500);
        addDeptRow(data['val'], name, hidden);
        addListeners();
    });
}

function addBonus() {
    if ($("#bonusstart").datetimepicker('date') === null || $("#bonusstop").datetimepicker('date') === null) {
        alert("Please select a start and stop date.");
        return;
    }
    const start = $("#bonusstart").datetimepicker('date').format("YYYY-MM-DD HH:mm:ss");
    const stop = $("#bonusstop").datetimepicker('date').format("YYYY-MM-DD HH:mm:ss");
    const depts = $("#depts").val();
    const modifier = $("#bonusmod").val();
    //console.log("Bonus: START: " + start + " STOP: " + stop + " DEPTS: " + depts + " MOD: " + modifier);

    postAction({
        action: 'addBonus',
        start: start,
        stop: stop,
        depts: depts.toString(),
        modifier: modifier
    }, function (data) {
        if (data['code'] === 0) return;
        addBonusRow(data['val'], start, stop, depts, modifier);
        toastNotify('Bonus added!', 'success', 1500);
    });
}

function updateDept(id, name, hidden) {
    postAction({action: 'updateDept', id: id, name: name, hidden: hidden}, function (data) {
        if (data['code'] === 0) return;
        toastNotify('Department updated.', 'success', 1500);
    });
}

function removeManager(elem) {
    setManager(0, getTableKey(elem), function () {
        $(elem).parent().parent().remove();
    });
}

function removeAdmin(elem) {
    setAdmin(0, getTableKey(elem), function () {
        $(elem).parent().parent().remove();
    });
}

function removeBanned(elem) {
    setBanned(0, getTableKey(elem), function () {
        $(elem).parent().parent().remove();
    });
}

function removeBonus(elem) {
    postAction({action: 'removeBonus', id: $(elem).data("id")}, function (data) {
        if (data['code'] === 0) return;
        $(elem).parent().parent().remove();
        toastNotify('Bonus removed.', 'success', 1500);
    });
}

function toggleSite(button) {
    toggleSetting(button, 'Disable', 'Enable', 'Disabling', 'Enabling', 'Site', 'setSiteStatus', 'btn-success btn-danger', function (data) {
        console.log("Site status changed.")
    })
}

function toggleKiosk(button) {
    toggleSetting(button, 'Deauthorize', 'Authorize', 'Deauthorizing', 'Authorizing', 'Kiosk', 'setKioskAuth', 'btn-warning btn-danger', function (data) {
        var expireDate = new Date;
        expireDate.setDate(expireDate.getDate() + 30);
        $.cookie("kiosknonce", data.val, {expires: expireDate});
    })
}

function toggleDevmode(button) {
    toggleSetting(button, 'Disable', 'Enable', 'Disabling', 'Enabling', 'Dev Mode', 'setDevmode', 'btn-warning btn-danger', function (data) {
        console.log("Dev mode changed.")
    })
}

function addUserRow(type, badge, name) {
    const data = [badge, name, "<button type=\"button\" class=\"btn btn-sm btn-danger\" onClick=\"remove" + type + "(this)\">Remove</button>"];
    addRow(true, $("#uRow" + type), data)
}

function addDeptRow(id, name, hidden) {
    const data = [id, "<input type=\"text\" data-type=\"dept\" class=\"form-control inputDark wt\" value=\"" + name + "\" aria-label=\"Name\" aria-describedby=\"basic-addon2\">", "<select class=\"custom-select inputDark wt\" data-type=\"dept\"><option value=0>No</option><option " + (hidden === 1 ? "selected=\"\"" : "") + "value=1>Yes</option></select>"];
    addRow(true, $("#dRow"), data)
}

function addBonusRow(id, start, stop, depts, modifier) {
    const data = [start, stop, depts, modifier, "<button type=\"button\" class=\"btn btn-sm btn-danger\" data-id=\"" + id + "\" onClick=\"removeBonus(this)\">Remove</button>"];
    addRow(false, $("#bRow"), data)
}

function addRow(key, elem, data) {
    let innerTable = '';
    for (let i = 0; i < data.length; i++) {
        const t = (key && i === 0) ? 'th' : 'td';
        innerTable += '<' + t + '>' + data[i] + '</' + t + '>';
    }
    $(elem).append('<tr>' + innerTable + '</tr>');
}

function changeFrame(type) {
    $('#datFrame').show();
    $('#datFrame').attr("src", "pages/report.php?type=" + type);
}