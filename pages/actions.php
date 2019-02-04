<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 1/31/2019
 * Time: 10:37 PM
 */
header('Content-type: application/json');

define('TRACKER', TRUE);

include('../includes/header.php');

$user = isValidSession($session, $badgeID);

$ret['code'] = 0;
//$ret['msg'] = "Unknown Action.";

if ($user == null) {
    $ret['msg'] = "Not authenticated.";
} elseif (!isset($_POST['action'])) {
    $ret['msg'] = "No data provided.";
} else {
    $action = $_POST['action'];

    if ($action == "checkIn") {
        $dept = $_POST['dept'];

        if ($dept == "-1") {
            $ret['code'] = 0;
            $ret['msg'] = "Invalid department specified.";
        } else {
            checkIn($badgeID, $dept);

            $ret['code'] = 1;
            $ret['msg'] = "Clocked in.";
            //$ret['msg'] = "Not Implemented ...YET!\nBUT HEY LOOK THERE'S A JSON \"API\" CALLBACK AT LEAST! \xF0\x9F\x98\x81";
        }
    } else if ($action == "checkOut") {
        checkOut($badgeID);

        $ret['code'] = 1;
        $ret['msg'] = "Clocked out.";
    } else if ($action == "getClockTime") {
        $ret['code'] = 1;
        $ret['val'] = getClockTime($badgeID);
    } else if ($action == "getMinutesToday") {
        $ret['code'] = 1;
        $ret['val'] = getMinutesToday($badgeID);
    } else if ($action == "getEarnedTime") {
        $ret['code'] = 1;
        $ret['val'] = calculateBonusTime($badgeID) + getMinutesTotal($badgeID);
    }
}

die(json_encode($ret));