function initData() {
    clockCycle();

    postAction({action: 'getDepts'}, function (data) {
        window.depts = data['val'];

        $.each(data['val'], function (index, value) {
            $('#dept').append($("<option></option>").attr("value", value['id']).text(value['name']));
        });

        $("#dept").selectpicker("refresh");
    });
}

$('#searchinput').on("input", debounce(function () {
    userSearch($("#searchinput").val())
}, 250));

function userSearch(input) {
    $("#uRow").empty();

    input = input.trim();
    if (input === '') return;

    postAction({action: 'getUserSearch', input: input}, function (data) {
        if (data['code'] === 0) return;

        $.each(data['results'], function (index, value) {
            addUserRow(value['id'], value['nickname'], value['first_name'] + " " + value['last_name'], value['dept'], value['banned']);
        });
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

function loadVolunteer(id) {
    onClock = false;
    shiftTime = 0;

    postAction({action: 'getUser', id: id}, function (data) {
        if (data['code'] === 0) return;
        $("#eRow").empty();
        const uCard = $("#uHeadName");
        const user = data['user'][0];

        console.log(user);

        uCard.parent().parent().show();
        uCard.text(user['nickname']);
        window.currUid = user['id'];
        initClock(user['id']);

        $("#rewards").find(`[data-type='${'reward'}']`).attr('class', 'btn btn-sm btn-danger');
        $("#rewards").find(`[data-type='${'reward'}']`).html('Claim');
        getRewardClaims(id, "time", function (data) {
            if (data.code === -1) return;
            $.each(data['val'], function (key, val) {
                let reward = $("#rewards").find(`[data-id='${val.claim}']`);
                reward.removeClass("btn-danger");
                reward.addClass("btn-success");
                reward.data("state", "claimed");
                reward.html("Claimed");
            });
        });

        // Load entries
        postAction({action: 'getTimeEntriesOther', id: id}, function (data) {
            $.each(data['val'], function (index, value) {
                const earned = value['worked'] + (value['bonus'] * 60);

                addEntryRow(value['id'], value['ongoing'], value['checkin'], value['checkout'], value['dept'], moment.duration(value['worked'], "seconds").format("h:mm:ss", {trim: "both"}), moment.duration(earned, "seconds").format("h:mm:ss", {trim: "both"}), value['notes'], value['auto']);
            });

            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })
        });
    });
}

function addTime() {
    const uid = window.currUid;
    const start = $("#timestart").datetimepicker('date').format("YYYY-MM-DD HH:mm:ss");
    const stop = $("#timestop").datetimepicker('date').format("YYYY-MM-DD HH:mm:ss");
    const dept = $("#dept").val();
    const notes = $("#notes").val();

    if ($("#timestart").datetimepicker('date') === null || $("#timestop").datetimepicker('date') === null) {
        alert("Please select a start and stop date.");
        return;
    }

    if (dept === undefined || dept === null) {
        alert("Please select a department.");
        return;
    }

    postAction({
        action: 'addTime',
        id: uid,
        start: start,
        stop: stop,
        dept: dept,
        notes: notes
    }, function (data) {
        if (data['code'] === 0) return;

        loadVolunteer(uid);
        toastNotify('Time added!', 'success', 1500);
    });
}

function checkIn() {
    const uid = window.currUid;
    const dept = $("#dept").val();
    const notes = $("#notes").val();

    if ($("#timestart").datetimepicker('date') !== null || $("#timestop").datetimepicker('date') !== null) {
        alert("Start/Stop date not required for Check In. Did you mean add time?");
        $("#timestart").datetimepicker().clear();
        $("#timestop").datetimepicker().clear();
        return;
    }

    if (dept === undefined || dept === null) {
        alert("Please select a department.");
        return;
    }

    postAction({
        action: 'checkInOther',
        id: uid,
        dept: dept,
        notes: notes
    }, function (data) {
        if (data['code'] === 0) return;

        loadVolunteer(uid);
        toastNotify('User checked in!', 'success', 1500);
    });
}

function removeTime(id) {
    postAction({action: "removeTime", id: id}, function (data) {
        loadVolunteer(window.currUid);
        toastNotify('Removed time entry!', 'success', 1500);
    });
}

function checkOutOther() {
    postAction({action: "checkOutOther", id: window.currUid}, function (data) {
        loadVolunteer(window.currUid);
        toastNotify('Checked out!', 'success', 1500);
    });
}

function getClockTime(id, callback) {
    postAction({action: "getClockTimeOther", id: id}, function (data) {
        callback(data);
    });
}

function getMinutesToday(id, callback) {
    postAction({action: "getMinutesTodayOther", id: id}, function (data) {
        callback(data);
    });
}

function getEarnedTime(id, callback) {
    postAction({action: "getEarnedTimeOther", id: id}, function (data) {
        callback(data);
    });
}

function getRewardClaims(id, type, callback) {
    postAction({action: "getRewardClaims", id: id, type: type}, function (data) {
        callback(data);
    });
}

function toggleClaim(button) {
    // Lazyness
    if ($(button).data('state') === "claimed") {
        postAction({action: "unclaimReward", uid: window.currUid, type: $(button).data('id')}, function (data) {
            $(button).removeClass("btn-success");
            $(button).addClass("btn-danger");
            $(button).data("state", "unclaimed");
            $(button).html("Claim");
        });
    } else {
        postAction({action: "claimReward", uid: window.currUid, type: $(button).data('id')}, function (data) {
            $(button).removeClass("btn-danger");
            $(button).addClass("btn-success");
            $(button).data("state", "claimed");
            $(button).html("Claimed");
        });
    }
}

function initClock(id) {
    getClockTime(id, function (data) {
        $('#currdurr').hide();

        if (data.val === -1) return;
        shiftTime = data.val;
        onClock = true;
        $('#currdurr').show();
        updateClock();
    });

    getMinutesToday(id, function (data) {
        if (data.val === -1) return;
        $('#hourstoday').html(Math.round((data.val / 60) * 10) / 10);
    });

    getEarnedTime(id, function (data) {
        if (data.val === -1) return;
        $('#earnedtime').html(Math.round((data.val / 60) * 10) / 10);
    });
}

function addUserRow(id, username, name, dept, banned) {
    let status = "<span class=\"badge badge-pill badge-" + (dept == null ? "warning" : "success") + "\">" + (dept == null ? "Checked Out" : "Checked In") + "</span>";
    if (banned === 1) status = status.concat("<span class=\"badge badge-pill badge-danger\">Banned</span>");
    const data = [id, username, name, status, "<button type=\"button\" class=\"btn btn-sm btn-info\" data-id=\"" + id + "\" onClick=\"loadVolunteer(getTableKey(this))\">Load</button>"];
    addRow(true, $("#uRow"), data)
}

function addEntryRow(id, ongoing, checkin, checkout, dept, worked, earned, notes, auto) {
    let notesPill = notes === "" ? "" : "<span data-toggle=\"tooltip\" title=\"" + notes + "\" class=\"badge badge-pill badge-primary\">Notes</span>";
    if (auto === 1) notesPill = notesPill.concat("<span class=\"badge badge-pill badge-warning\">AUTO</span>");
    const statusButton = !ongoing ? "" : "<button type=\"button\" class=\"btn btn-sm btn-warning\" data-id=\"" + id + "\" onClick=\"checkOutOther()\">Checkout</button>";
    const data = [checkin, ongoing ? "Ongoing..." : checkout, window.depts[dept]['name'], worked, earned, notesPill, statusButton + "<button type=\"button\" class=\"btn btn-sm btn-info\" data-id=\"" + id + "\" onClick=\"removeTime($(this).data('id'))\">Delete</button>"];
    addRow(false, $("#eRow"), data)
}