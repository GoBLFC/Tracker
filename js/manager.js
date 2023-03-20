var logoutTime = 900;

const timeStart = new tempusDominus.TempusDominus(document.getElementById("timeStart"));
const timeStop = new tempusDominus.TempusDominus(document.getElementById("timeStop"));

function initData() {
    clockCycle();

    postAction("/api/manage.php", {action: 'getDepts'}, function (data) {
        window.depts = data['val'];

        $.each(data['val'], function (index, value) {
            $('#dept').append($("<option></option>").attr("value", value['id']).text(value['name']));
        });

        // $("#dept").selectpicker("refresh");
    });
}

$('#searchinput').on("input", debounce(function () {
    userSearch($("#searchinput").val())
}, 250));

function userSearch(input) {
    $("#uRow").empty();

    input = input.trim();
    if (input === '') return;

    postAction("/api/manage.php", {action: 'getUserSearch', input: input}, function (data) {
        if (data['code'] === 0) return;

        $.each(data['results'], function (index, value) {
            addUserRow(value['id'], value['username'], value['first_name'] + " " + value['last_name'], value['dept'], value['banned']);
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

    postAction("/api/manage.php", {action: 'getUser', id: id}, function (data) {
        if (data['code'] === 0) return;
        $("#eRow").empty();
        const user = data['user'];

        console.log(user);

        $("#userCard").removeClass("d-none");
        $("#userCardTitle").text(user['username']);
        window.currUid = user['id'];
        initClock(user['id']);

        $("#rewards").find(`[data-type='${'reward'}']`).attr('class', 'btn btn-sm btn-danger');
        $("#rewards").find(`[data-type='${'reward'}']`).html('Claim');
        getRewardClaims(id, function (data) {
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
        postAction("/api/manage.php", {action: 'getTimeEntriesOther', id: id}, function (data) {
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
    const start = moment(timeStart.dates.picked[0]).format("YYYY-MM-DD HH:mm:ss");
    const stop = moment(timeStop.dates.picked[0]).format("YYYY-MM-DD HH:mm:ss");
    const dept = $("#dept").val();
    const notes = $("#notes").val();

    if (moment(timeStart.dates.picked[0]) == null || moment(timeStop.dates.picked[0]) == null) {
        alert("Please select a start and stop date.");
        return;
    }

    if (dept === undefined || dept === null) {
        alert("Please select a department.");
        return;
    }

    postAction("/api/manage.php", {
        action: 'addTime',
        id: uid,
        start: start,
        stop: stop,
        dept: dept,
        notes: notes
    }, function (data) {
        if (data['code'] === 0) return;

        loadVolunteer(uid);
        Toast.fire({
            text: "Time added",
            icon: "success"
        });
    });
}

function createUser(badgeid) {
    postAction("/api/manage.php", {action: 'createUser', badgeid: badgeid}, function (data) {
        if (data['code'] === 0) {
            Toast.fire({
                text: "User (" + badgeid + ") already exists",
                icon: "warning"
            });
        } else {
            Toast.fire({
                text: "User (" + badgeid + ") created",
                icon: "success"
            });
        }
    });
}

function checkIn() {
    const uid = window.currUid;
    const dept = $("#dept").val();
    const notes = $("#notes").val();
    var start = null;

    if (moment(timeStart.dates.picked[0]) != null) start = moment(timeStart.dates.picked[0]).format("YYYY-MM-DD HH:mm:ss");

    if (moment(timeStart.dates.picked[0]) != null && moment(timeStop.dates.picked[0]) != null) {
        alert("Please use 'Add Time' when Start and Stop dates are filled in.");
        return;
    }

    if (dept === undefined || dept === null || dept == "") {
        alert("Please select a department.");
        return;
    }

    postAction("/api/manage.php", {
        action: 'checkInOther',
        id: uid,
		start: start,
        dept: dept,
        notes: notes
    }, function (data) {
        if (data['code'] === 0) return;

        loadVolunteer(uid);
        Toast.fire({
            text: "User checked in",
            icon: "success"
        });
    });
}

function removeTime(id) {
    postAction("/api/manage.php", {action: "removeTime", id: id}, function (data) {
        loadVolunteer(window.currUid);
        Toast.fire({
            text: "Removed time entry",
            icon: "success"
        });
    });
}

function checkOutOther() {
	console.log(window.currUid);
    postAction("/api/manage.php", {action: "checkOutOther", id: window.currUid}, function (data) {
        loadVolunteer(window.currUid);
		if (data['code'] === 1) {
			Toast.fire({
                text: "Checked out",
                icon: "success"
            });
        } else {
            Toast.fire({
                text: data["msg"],
                icon: "success"
            });
        }
    });
}

function getClockTime(id, callback) {
    postAction("/api/manage.php", {action: "getClockTimeOther", id: id}, function (data) {
        callback(data);
    });
}

function getMinutesToday(id, callback) {
    postAction("/api/manage.php", {action: "getMinutesTodayOther", id: id}, function (data) {
        callback(data);
    });
}

function getEarnedTime(id, callback) {
    postAction("/api/manage.php", {action: "getEarnedTimeOther", id: id}, function (data) {
        callback(data);
    });
}

function getRewardClaims(id, callback) {
    postAction("/api/manage.php", {action: "getRewardClaims", id: id}, function (data) {
        callback(data);
    });
}

function toggleClaim(button) {
    // Lazyness
    if ($(button).data('state') === "claimed") {
		let claimConfirm = confirm("Are you sure to un-claim this reward?");

		if (claimConfirm){
			postAction("/api/manage.php", {action: "unclaimReward", uid: window.currUid, type: $(button).data('id')}, function (data) {
				$(button).removeClass("btn-success");
				$(button).addClass("btn-danger");
				$(button).data("state", "unclaimed");
				$(button).html("Claim");
			});
		}
    } else {
        postAction("/api/manage.php", {action: "claimReward", uid: window.currUid, type: $(button).data('id')}, function (data) {
            $(button).removeClass("btn-danger");
            $(button).addClass("btn-success");
            $(button).data("state", "claimed");
            $(button).html("Claimed");
        });
    }
}

function toggleKiosk(button) {
    toggleSetting(button, 'Deauthorize', 'Authorize', 'Deauthorizing', 'Authorizing', 'Kiosk', 'setKioskAuth', 'btn-warning btn-danger', function (data) {
        var expireDate = new Date;
        expireDate.setDate(expireDate.getDate() + 30);
        document.cookie = "kiosknonce=" + data.val + "; expires=" + expireDate.toUTCString() + ";";
    })
}

function toggleSetting(button, off, on, offLoad, onLoad, setting, method, toggle, callback) {
    const status = $(button).data('status');
    const opposite = !Number(status);

    applyLoading(button, (status === 0) ? offLoad : onLoad + ' ' + setting);

    $(button).data('status', opposite);
    postAction("/api/manage.php", {action: method, status: +opposite}, function (data) {
        const statusTextOpposite = ((+opposite === 0) ? off : on);
        const statusText = ((+opposite === 1) ? off : on);

        $(button).html($(button).data('original-text'));

        if (data.code === 1) {
            $(button).html(statusText + ' ' + setting);
            $(button).toggleClass(toggle);

            Toast.fire({
                text: setting + " " + statusTextOpposite + "d!",
                icon: "success"
            });

            if (callback !== null) callback(data);
        }
    });
}

function initClock(id) {
    getClockTime(id, function (data) {
        $('#currdurr').addClass("d-none");

        if (data.val === -1) return;
        shiftTime = data.val;
        onClock = true;
        $('#currdurr').removeClass("d-none");
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
    let status = "<span class=\"badge rounded-pill text-bg-" + (dept == null ? "warning" : "success") + "\">" + (dept == null ? "Checked Out" : "Checked In") + "</span>";
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