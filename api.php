<?php

header('Content-type: application/json');

require "main.php";

$ret['code'] = -1;
//$ret['msg'] = "Unknown Action.";

if ($user == null && isset($_POST['action']) && $_POST['action'] == "checkQuickCode") {
    $auth = $db->checkQuickCode($_POST['quickcode']);
    if (!$auth) {
        $ret['code'] = -1;
    } else {
        $ret['code'] = 1;
        //$ret['msg'] = "Good Code! " + $result[0]['id'];
        $ret['id'] = $result[0]['id'];
        $ret['session'] = userSignIn($result[0]['id'], $result[0]['first_name'], $result[0]['last_name'], $result[0]['nickname']);
    }
} elseif ($user == null) {
    $ret['code'] = 0;
    $ret['msg'] = "Not authenticated.";
} elseif (!isset($_POST['action'])) {
    $ret['code'] = 0;
    $ret['msg'] = "No data provided.";
} else if ((!$isAdmin && !$isManager) && !$db->getDevMode() && !$db->checkKiosk($_COOKIE['kiosknonce'])->fetch()) {
    $ret['code'] = 0;
    $ret['msg'] = "Kiosk not authorized.";
} else if ((!$isAdmin && !$isManager) && !$db->getSiteStatus()) {
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
            $db->checkIn($badgeID, $dept, "", $badgeID);

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
        $ret['val'] = $db->listNotifications($badgeID, 0)->fetchAll();
    } else if ($action == "readNotification") {
        $ret['code'] = 1;
        $ret['val'] = $db->markNotificationRead($_POST['id']);
    } else if ($action == "ackAllNotifs") {
        $ret['code'] = 1;
        $ret['val'] = $db->ackAllNotifs($badgeID);
    }

    // MANAGER FUNCTIONS
    if (($isManager || $isAdmin || $isLead) && substr($action, 0, 3) !== "get") {
        $postData = array();
        foreach ($_POST as $p => $d) {
            if ($p == "action") continue;
            $postData[] = "$p:$d";
        }

        $db->createLog($_SESSION['badgeid'], $action, implode(",", $postData));
    }
	
    if ((!$isLead && !$isAdmin && !$isManager) && $ret['code'] === -1) {
		$ret['code'] = 0;
        $ret['msg'] = "Unauthorized.";
	}else{
		if ($action == "setKioskAuth") {
            $status = $_POST['status'];

            if ($status == 1) {
                $kioskNonce = md5(rand());
                $db->authorizeKiosk($kioskNonce);
                $ret['val'] = $kioskNonce;
            }

            if ($status == 0) {
                $db->deauthorizeKiosk($_COOKIE['kiosknonce']);
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
            $users = $db->searchUsers($input)->fetchAll();
            foreach ($users as $user) {
                $dept = $db->getCheckIn($user['id'])->fetch();
                if (isset($dept)) $user['dept'] = $dept;
                $ret['results'][] = $user;
            }
        } else if ($action == "getDepts") {
            $depts = array();
            foreach ($db->listDepartments() as $dept) $depts[$dept['id']] = $dept;
            $ret['val'] = $depts;
            $ret['code'] = 1;
        } else if ($action == "getUser") {
            $ret['code'] = 1;
            $ret['user'] = $db->getUser($_POST['id'])->fetch();
            $dept = $db->getCheckIn($_POST['id'])->fetch();
            $ret['user']['dept'] = isset($dept) ? $dept : null;
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
            $db->checkIn($_POST['id'], $_POST['dept'], $_POST['notes'], $badgeID, $_POST['start']);
        } else if ($action == "createUser") {
            $badgeID = $_POST['badgeid'];
            $ret['code'] = createUser($badgeID);
        } else if ($action == "addTime") {
            $id = $_POST['id'];
            $start = $_POST['start'];
            $stop = $_POST['stop'];
            $dept = $_POST['dept'];
            $notes = $_POST['notes'];

            $ret['val'] = $db->createTime($id, $start, $stop, $dept, $notes, $badgeID);
            $ret['code'] = 1;
        } else if ($action == "removeTime") {
            $ret['code'] = 1;
            $db->deleteTime($_POST['id']);
        } else if ($action == "getRewardClaims") {
            $ret['code'] = 1;
            $ret['val'] = $db->listRewardClaims($_POST['id']);
        } else if ($action == "claimReward") {
            $ret['code'] = 1;
            $ret['val'] = $db->claimReward($_POST['uid'], $_POST['type']);
        } else if ($action == "unclaimReward") {
            $ret['code'] = 1;
            $ret['val'] = $db->unclaimReward($_POST['uid'], $_POST['type']);
        } else if ($action == "setKioskAuth") {
            $status = $_POST['status'];

            if ($status == 1) {
                $kioskNonce = md5(rand());
                $db->authorizeKiosk($kioskNonce);
                $ret['val'] = $kioskNonce;
            }

            if ($status == 0) {
                $db->deauthorizeKiosk($_COOKIE['kiosknonce']);
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
            $ret['val'] = $db->setSiteStatus($status);
        } else if ($action == "setDevmode") {
            $status = $_POST['status'];
            $ret['code'] = 1;
            $ret['val'] = $db->setDevmode($status);
        } else if ($action == "setAdmin") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = $db->getUser($badgeID)->fetch();

            if ($_SESSION['badgeid'] == $badgeID) {
                $ret['code'] = 0;
                $ret['msg'] = "You can't remove yourself!!";
            } else if (!isset($user)) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                $db->setUserRole($badgeID, admin: $value);
                $ret['name'] = $user['nickname'];
                $ret['code'] = 1;
            }
        } else if ($action == "setManager") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = $db->getUser($badgeID)->fetch();
            if (!isset($user)) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                $db->setUserRole($badgeID, manager: $value);
                $ret['name'] = $user['nickname'];
                $ret['code'] = 1;
            }
        } else if ($action == "setLead") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = $db->getUser($badgeID)->fetch();
            if (!isset($user)) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                $db->setUserRole($badgeID, lead: $value);
                $ret['name'] = $user['nickname'];
                $ret['code'] = 1;
            }
		} else if ($action == "setBanned") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $ret['name'] = setBanned($badgeID, $value);
        } else if ($action == "getAdmins") {
            $ret['val'] = $db->listUsersByRole(admin: true)->fetchAll();
            $ret['code'] = 1;
        } else if ($action == "getManagers") {
            $ret['val'] = $db->listUsersByRole(manager: true)->fetchAll();
            $ret['code'] = 1;
        } else if ($action == "getLeads") {
            $ret['val'] = $db->listUsersByRole(lead: true)->fetchAll();
            $ret['code'] = 1;
        } else if ($action == "getBanned") {
            $ret['val'] = $db->listBans()->fetchAll();
            $ret['code'] = 1;
        } else if ($action == "getDepts") {
            $depts = array();
            foreach ($db->listDepartments() as $dept) $depts[$dept['id']] = $dept;
            $ret['val'] = $depts;
            $ret['code'] = 1;
        } else if ($action == "getBonuses") {
            $ret['val'] = $db->listBonuses()->fetchAll();
            $ret['code'] = 1;
        } else if ($action == "getRewards") {
            $ret['val'] = $db->listRewards(hidden: true)->fetchAll();
            $ret['code'] = 1;
        } else if ($action == "addDept") {
            $name = $_POST['name'];
            $hidden = $_POST['hidden'];

            $ret['val'] = $db->createDepartment($name, $hidden);
            $ret['code'] = 1;
        } else if ($action == "addReward") {
            $name = $_POST['name'];
            $desc = $_POST['description'];
            $hours = $_POST['hours'];
            $hidden = $_POST['hidden'];
            $type = "other";
            if ($hours > 0) $type = "time";

            $ret['val'] = $db->createReward($name, $desc, $hours, $type, $hidden);
            $ret['code'] = 1;
        } else if ($action == "updateDept") {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $hidden = $_POST['hidden'];

            $ret['val'] = $db->updateDepartment($id, $name, $hidden);
            $ret['code'] = 1;
        } else if ($action == "updateReward") {
            $id = $_POST['id'];
            $field = $_POST['field'];
            $value = $_POST['value'];
            $type = "other";

            if ($field == "hours" && $value > 0) $type = "time";

            $ret['val'] = $db->updateReward($id, $field, $value, $type);
            $ret['code'] = 1;
        } else if ($action == "removeBonus") {
            $id = $_POST['id'];

            $ret['val'] = $db->deleteBonus($id);
            $ret['code'] = 1;
        } else if ($action == "addBonus") {
            $start = $_POST['start'];
            $stop = $_POST['stop'];
            $depts = $_POST['depts'];
            $modifier = $_POST['modifier'];

            $ret['val'] = $db->createBonus($start, $stop, $depts, $modifier);
            $ret['code'] = 1;
        } else if ($action == "getApps") {
            $ret['val'] = getApps();
            $ret['code'] = 1;
        }
    }
}

die(json_encode($ret));