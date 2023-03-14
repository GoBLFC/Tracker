<?php

header("Content-Type: application/json");

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
}

die(json_encode($ret));
