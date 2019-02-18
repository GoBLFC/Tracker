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
$isAdmin = isAdmin($badgeID);

$ret['code'] = -1;
//$ret['msg'] = "Unknown Action.";

if ($user == null) {
    $ret['code'] = 0;
    $ret['msg'] = "Not authenticated.";
} elseif (!isset($_POST['action'])) {
    $ret['code'] = 0;
    $ret['msg'] = "No data provided.";
} else if (!$isAdmin && sizeof(checkKiosk($_COOKIE['kiosknonce'])) == 0) {
    $ret['code'] = 0;
    $ret['msg'] = "Kiosk not authorized.";
} else if (!$isAdmin && getSiteStatus() == 0) {
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

    // MANAGER FUNCTIONS
    /*
     *
     */

    // ADMIN FUNCTIONS
    if (!$isAdmin && $ret['code'] === -1) {
        $ret['code'] = 0;
        $ret['msg'] = "Unauthorized.";
    } else {
        if ($action == "setSiteStatus") {
            $status = $_POST['status'];
            $ret['code'] = 1;
            $ret['val'] = setSiteStatus($status);
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
        } else if ($action == "setAdmin") {
            $badgeID = $_POST['badgeid'];
            $value = $_POST['value'];

            $user = getUserByID($badgeID);

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

            $user = getUserByID($badgeID);
            if (!isset($user[0])) {
                $ret['code'] = 0;
                $ret['msg'] = "User with ID '$badgeID' not found!";
            } else {
                setManager($value, $badgeID);
                $ret['name'] = $user[0]['nickname'];
                $ret['code'] = 1;
            }
        } else if ($action == "getAdmins") {
            $ret['val'] = getAdmins();
            $ret['code'] = 1;
        } else if ($action == "getManagers") {
            $ret['val'] = getManagers();
            $ret['code'] = 1;
        } else if ($action == "getDepts") {
            $ret['val'] = getDepts();
            $ret['code'] = 1;
        } else if ($action == "getBonuses") {
            $ret['val'] = getBonuses();
            $ret['code'] = 1;
        } else if ($action == "addDept") {
            $name = $_POST['name'];
            $hidden = $_POST['hidden'];

            $ret['val'] = addDept($name, $hidden);
            $ret['code'] = 1;
        } else if ($action == "updateDept") {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $hidden = $_POST['hidden'];

            $ret['val'] = updateDept($id, $name, $hidden);
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
        }
    }
}

die(json_encode($ret));