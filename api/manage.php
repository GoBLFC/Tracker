<?php

header("Content-Type: application/json");

require "../main.php";

$ret['code'] = -1;
//$ret['msg'] = "Unknown Action.";

$action = $_POST['action'];

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
            if ($dept) $user['dept'] = $dept;
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
        $ret['val'] = $db->listRewardClaims($_POST['id'])->fetchAll();
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

die(json_encode($ret));

?>
