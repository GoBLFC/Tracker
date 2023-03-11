<?php

header('Content-type: application/json');

require "main.php";

$ret['code'] = -1;
//$ret['msg'] = "Unknown Action.";

if ($user == null && isset($_POST['action']) && $_POST['action'] == "checkQuickCode") {
	$ret = checkQuickCode($_POST['quickcode']);
} elseif ($user == null) {
    $ret['code'] = 0;
    $ret['msg'] = "Not authenticated.";
} elseif (!isset($_POST['action'])) {
    $ret['code'] = 0;
    $ret['msg'] = "No data provided.";
} else if ((!$isAdmin && !$isManager) && getDevmode() == 0 && sizeof(checkKiosk($_COOKIE['kiosknonce'])) == 0) {
    $ret['code'] = 0;
    $ret['msg'] = "Kiosk not authorized.";
} else if ((!$isAdmin && !$isManager) && getSiteStatus() == 0) {
    $ret['code'] = 0;
    $ret['msg'] = "Site is disabled.";
} else {
    $action = $_POST['action'];

    if ($action == "checkIn") {
        $dept = $_POST['dept'];

        if ($dept == "-1") {
            $ret['code'] = 0;
            $ret['msg'] = "Invalid department specified.";
        } else {
            checkIn($badgeID, null, $dept, "", $badgeID);

            $ret['code'] = 1;
            $ret['msg'] = "Clocked in.";
            //$ret['msg'] = "Not Implemented ...YET!\nBUT HEY LOOK THERE'S A JSON \"API\" CALLBACK AT LEAST! \xF0\x9F\x98\x81";
        }
    } else if ($action == "checkOut") {
        $ret = checkOut($badgeID, null, $ret);
    } else if ($action == "getClockTime") {
        $ret['code'] = 1;
        $ret['val'] = getClockTime($badgeID);
    } else if ($action == "getMinutesToday") {
        $ret['code'] = 1;
        $ret['val'] = getMinutesToday($badgeID);
    } else if ($action == "getEarnedTime") {
        $ret['code'] = 1;
        $ret['val'] = calculateBonusTime($badgeID, false) + getMinutesTotal($badgeID);
    } else if ($action == "getNotifications") {
        $ret['code'] = 1;
        $ret['val'] = getNotifications($badgeID, 0);
    } else if ($action == "readNotification") {
        $ret['code'] = 1;
        $ret['val'] = markNotificationRead($_POST['id']);
    } else if ($action == "ackAllNotifs") {
        $ret['code'] = 1;
        $ret['val'] = ackAllNotifs($badgeID);
    }

    // MANAGER FUNCTIONS
    if (($isManager || $isAdmin || $isLead) && substr($action, 0, 3) !== "get") {
        $postData = array();
        foreach ($_POST as $p => $d) {
            if ($p == "action") continue;
            $postData[] = "$p:$d";
        }

        addLog($_SESSION['badgeid'], $action, implode(",", $postData));
    }
	
    if ((!$isLead && !$isAdmin && !$isManager) && $ret['code'] === -1) {
		$ret['code'] = 0;
        $ret['msg'] = "Unauthorized.";
	}else{
		if ($action == "setKioskAuth") {
            $status = $_POST['status'];

            if ($status == 1) {
                $kioskNonce = md5(rand());
                authorizeKiosk($kioskNonce);
                $ret['val'] = $kioskNonce;
            }

            if ($status == 0) {
                deauthorizeKiosk($_COOKIE['kiosknonce']);
                $ret['val'] = 1;
            }

            $ret['code'] = 1;
		}
	}

    if ((!$isManager && !$isAdmin) && $ret['code'] === -1) {
        $ret['code'] = 0;
        $ret['msg'] = "Unauthorized.";
    } else {
        if ($action == "getUserSearch") {
            $input = $_POST['input'];
            $ret['code'] = 1;
            $users = findUser($input);
            foreach ($users as $user) {
                $dept = getCheckIn($user['id']);
                if (isset($dept[0])) $user['dept'] = $dept;
                $ret['results'][] = $user;
            }
        } else if ($action == "getDepts") {
            $depts = array();
            foreach (getDepts() as $dept) $depts[$dept['id']] = $dept;
            $ret['val'] = $depts;
            $ret['code'] = 1;
        } else if ($action == "getUser") {
            $ret['code'] = 1;
            $ret['user'] = getUserByID($_POST['id'], false);
            $dept = getCheckIn($_POST['id']);
            $ret['user'][0]['dept'] = isset($dept[0]) ? $dept[0] : null;
        } else if ($action == "getClockTimeOther") {
            $ret['code'] = 1;
            $ret['val'] = getClockTime($_POST['id']);
        } else if ($action == "getMinutesTodayOther") {
            $ret['code'] = 1;
            $ret['val'] = getMinutesToday($_POST['id']);
        } else if ($action == "getEarnedTimeOther") {
            $ret['code'] = 1;
            $ret['val'] = calculateBonusTime($_POST['id'], false) + getMinutesTotal($_POST['id']);
        } else if ($action == "getTimeEntriesOther") {
            $ret['code'] = 1;
            $ret['val'] = calculateBonusTime($_POST['id'], true);
        } else if ($action == "checkOutOther") {
            $ret = checkOut($_POST['id'], null, null);
        } else if ($action == "checkInOther") {
            $ret['code'] = 1;
            checkIn($_POST['id'], $_POST['start'], $_POST['dept'], $_POST['notes'], $badgeID);
        } else if ($action == "createUser") {
            $badgeID = $_POST['badgeid'];
            $ret['code'] = createUser($badgeID);
        } else if ($action == "addTime") {
            $id = $_POST['id'];
            $start = $_POST['start'];
            $stop = $_POST['stop'];
            $dept = $_POST['dept'];
            $notes = $_POST['notes'];

            $ret['val'] = addTime($id, $start, $stop, $dept, $notes, $badgeID);
            $ret['code'] = 1;
        } else if ($action == "removeTime") {
            $ret['code'] = 1;
            removeTime($_POST['id']);
        } else if ($action == "getRewardClaims") {
            $ret['code'] = 1;
            $ret['val'] = getRewardClaims($_POST['id']);
        } else if ($action == "claimReward") {
            $ret['code'] = 1;
            $ret['val'] = claimReward($_POST['uid'], $_POST['type']);
        } else if ($action == "unclaimReward") {
            $ret['code'] = 1;
            $ret['val'] = unclaimReward($_POST['uid'], $_POST['type']);
        } else if ($action == "setKioskAuth") {
            $status = $_POST['status'];

            if ($status == 1) {
                $kioskNonce = md5(rand());
                authorizeKiosk($kioskNonce);
                $ret['val'] = $kioskNonce;
            }

            if ($status == 0) {
                deauthorizeKiosk($_COOKIE['kiosknonce']);
                $ret['val'] = 1;
            }

            $ret['code'] = 1;
		}
    }

    // ADMIN FUNCTIONS
    if (!$isAdmin && $ret['code'] === -1) {
        $ret['code'] = 0;
        $ret['msg'] = "Unauthorized.";
    } else {
        if ($action == "setSiteStatus") {
            $status = $_POST['status'];
            $ret['code'] = 1;
            $ret['val'] = setSiteStatus($status);
        } else if ($action == "setDevmode") {
            $status = $_POST['status'];
            $ret['code'] = 1;
            $ret['val'] = setDevmode($status);
        } else if ($action == "setAdmin") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = getUserByID($badgeID, true);

            if ($_SESSION['badgeid'] == $badgeID) {
                $ret['code'] = 0;
                $ret['msg'] = "You can't remove yourself!!";
            } else if (!isset($user[0])) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                setAdmin($value, $badgeID);
                $ret['name'] = $user[0]['nickname'];
                $ret['code'] = 1;
            }
        } else if ($action == "setManager") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = getUserByID($badgeID, true);
            if (!isset($user[0])) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                setManager($value, $badgeID);
                $ret['name'] = $user[0]['nickname'];
                $ret['code'] = 1;
            }
        } else if ($action == "setLead") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = getUserByID($badgeID, true);
            if (!isset($user[0])) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                setLead($value, $badgeID);
                $ret['name'] = $user[0]['nickname'];
                $ret['code'] = 1;
            }
		} else if ($action == "setBanned") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $ret['name'] = setBanned($badgeID, $value);
        } else if ($action == "getAdmins") {
            $ret['val'] = getAdmins();
            $ret['code'] = 1;
        } else if ($action == "getManagers") {
            $ret['val'] = getManagers();
            $ret['code'] = 1;
        } else if ($action == "getLeads") {
            $ret['val'] = getLeads();
            $ret['code'] = 1;
        } else if ($action == "getBanned") {
            $ret['val'] = getBanned();
            $ret['code'] = 1;
        } else if ($action == "getDepts") {
            $depts = array();
            foreach (getDepts() as $dept) $depts[$dept['id']] = $dept;
            $ret['val'] = $depts;
            $ret['code'] = 1;
        } else if ($action == "getBonuses") {
            $ret['val'] = getBonuses();
            $ret['code'] = 1;
        } else if ($action == "getRewards") {
            $ret['val'] = getRewards(false, true);
            $ret['code'] = 1;
        } else if ($action == "addDept") {
            $name = $_POST['name'];
            $hidden = $_POST['hidden'];

            $ret['val'] = addDept($name, $hidden);
            $ret['code'] = 1;
        } else if ($action == "addReward") {
            $name = $_POST['name'];
            $desc = $_POST['description'];
            $hours = $_POST['hours'];
            $hidden = $_POST['hidden'];
            $type = "other";
            if ($hours > 0) $type = "time";

            $ret['val'] = addReward($name, $desc, $hours, $type, $hidden);
            $ret['code'] = 1;
        } else if ($action == "updateDept") {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $hidden = $_POST['hidden'];

            $ret['val'] = updateDept($id, $name, $hidden);
            $ret['code'] = 1;
        } else if ($action == "updateReward") {
            $id = $_POST['id'];
            $field = $_POST['field'];
            $value = $_POST['value'];
            $type = "other";

            if ($field == "hours" && $value > 0) $type = "time";

            $ret['val'] = updateReward($id, $field, $value, $type);
            $ret['code'] = 1;
        } else if ($action == "removeBonus") {
            $id = $_POST['id'];

            $ret['val'] = removeBonus($id);
            $ret['code'] = 1;
        } else if ($action == "addBonus") {
            $start = $_POST['start'];
            $stop = $_POST['stop'];
            $depts = $_POST['depts'];
            $modifier = $_POST['modifier'];

            $ret['val'] = addBonus($start, $stop, $depts, $modifier);
            $ret['code'] = 1;
        } else if ($action == "getApps") {
            $ret['val'] = getApps();
            $ret['code'] = 1;
        }
    }
}

die(json_encode($ret));