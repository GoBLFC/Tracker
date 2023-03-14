<?php

header("Content-Type: application/json");

require "main.php";

if ($user == null) {
    http_response_code(401);
    echo json_encode(["code" => 0, "msg" => "Not authenticated"]);
    exit();
} else if (!isset($_POST["action"])) {
    http_response_code(400);
    echo json_encode(["code" => 0, "msg" => "No data provided"]);
    exit();
} else if (!($isManager || $isAdmin)) {
    if (!$db->getDevMode() && !$db->checkKiosk($_COOKIE["kiosknonce"])->fetch()) {
        http_response_code(403);
        echo json_encode(["code" => 0, "msg" => "Kiosk not authorized"]);
        exit();
    } else if (!$db->getSiteStatus()) {
        http_response_code(403);
        echo json_encode(["code" => 0, "msg" => "Site is disabled"]);
        exit();
    }
}

$action = $_POST["action"];

switch ($action) {
    case "checkIn":
        $dept = $_POST["dept"];

        if ($dept == "-1") {
            echo json_encode(["code" => 0, "msg" => "Invalid department specified"]);
        } else {
            $db->checkIn($badgeID, $dept, "", $badgeID);
            echo json_encode(["code" => 1, "msg" => "Clocked in"]);
        }
        break;
    case "checkOut":
        echo json_encode(checkOut($badgeID, null));
        break;
    case "getClockTime":
        echo json_encode(["code" => 1, "val" => getClockTime($badgeID)]);
        break;
    case "getMinutesToday":
        echo json_encode(["code" => 1, "val" => getMinutesToday($badgeID)]);
        break;
    case "getEarnedTime":
        echo json_encode(["code" => 1, "val" => calculateBonusTime($badgeID, false) + getMinutesTotal($badgeID)]);
        break;
    case "getNotifications":
        echo json_encode(["code" => 1, "val" => $db->listNotifications($badgeID, 0)->fetchAll()]);
        break;
    case "readNotification":
        echo json_encode(["code" => 1, "val" => $db->markNotificationRead($_POST['id'])]);
        break;
    case "ackAllNotifs":
        echo json_encode(["code" => 1, "val" => $db->ackAllNotifs($badgeID)]);
        break;
}

// Logging
if (($isManager || $isAdmin || $isLead) && substr($action, 0, 3) !== "get") {
    $postData = [];
    foreach ($_POST as $p => $d) {
        if ($p == "action") continue;
        $postData[] = "$p:$d";
    }

    $db->createLog($_SESSION["badgeid"], $action, implode(",", $postData));
}

?>
