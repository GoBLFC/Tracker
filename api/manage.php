<?php

require "../api.php";

if (!($isManager || $isAdmin)) {
    http_response_code(403);
    echo json_encode(["code" => 0, "msg" => "Unauthorized"]);
    exit();
}

switch ($action) {
    case "getUserSearch":
        $users = $db->searchUsers($_POST["input"]);
        $results = [];

        foreach ($users as $user) {
            $dept = $db->getCheckIn($user["id"])->fetch();
            if ($dept) $user["dept"] = $dept;
            $results[] = $user;
        }

        echo json_encode(["code" => 1, "results" => $results]);
        break;
    case "getDepts":
        $depts = [];
        foreach ($db->listDepartments() as $dept) $depts[$dept["id"]] = $dept;
        echo json_encode(["code" => 1, "val" => $depts]);
        break;
    case "getUser":
        $id = $_POST["id"];

        $user = $db->getUser($id)->fetch();
        $dept = $db->getCheckIn($id)->fetch();
        $user["dept"] = $dept ? $dept : null;
        echo json_encode(["code" => 1, "user" => $user]);
        break;
    case "getClockTimeOther":
        echo json_encode(["code" => 1, "val" => getClockTime($_POST["id"])]);
        break;
    case "getMinutesTodayOther":
        echo json_encode(["code" => 1, "val" => getMinutesToday($_POST["id"])]);
        break;
    case "getEarnedTimeOther":
        echo json_encode(["code" => 1, "val" => calculateBonusTime($_POST["id"], false) + getMinutesTotal($_POST["id"])]);
        break;
    case "getTimeEntriesOther":
        echo json_encode(["code" => 1, "val" => calculateBonusTime($_POST["id"], true)]);
        break;
    case "checkOutOther":
        $result = $db->checkOut($_POST["id"], null);

        if (!$result->rowCount()) {
            echo json_encode(["code" => 0, "msg" => "Already checked out"]);
            break;
        }

        echo json_encode(["code" => 1, "msg" => "Checked out"]);
        break;
    case "checkInOther":
        $result = $db->checkIn($_POST["id"], $_POST["dept"], $_POST["notes"], $badgeID, $_POST["start"]);

        if (!$result) {
            echo json_encode(["code" => 0, "msg" => "Already checked in"]);
            break;
        }

        echo json_encode(["code" => 1, "msg" => "Checked in"]);
        break;
    case "createUser":
        echo json_encode(["code" => createUser($_POST["badgeID"])]);
        break;
    case "addTime":
        echo json_encode(["code" => 1, "val" => $db->createTime($_POST["id"], $_POST["start"], $_POST["stop"], $_POST["dept"], $_POST["notes"], $badgeID)]);
        break;
    case "removeTime":
        $db->deleteTime($_POST["id"]);
        echo json_encode(["code" => 1]);
        break;
    case "getRewardClaims":
        echo json_encode(["code" => 1, "val" => $db->listRewardClaims($_POST["id"])->fetchAll()]);
        break;
    case "claimReward":
        echo json_encode(["code" => 1, "val" => $db->claimReward($_POST["uid"], $_POST["type"])]);
        break;
    case "unclaimReward":
        echo json_encode(["code" => 1, "val" => $db->unclaimReward($_POST["uid"], $_POST["type"])]);
        break;
    case "setKioskAuth":
        $status = $_POST["status"];

        if ($status == 1) {
            $kioskNonce = bin2hex(random_bytes(16));
            $db->authorizeKiosk($kioskNonce);
            echo json_encode(["code" => 1, "val" => $kioskNonce]);
        }

        if ($status == 0) {
            $db->deauthorizeKiosk($_COOKIE["kiosknonce"]);
            echo json_encode(["code" => 1, "val" => 1]);
        }
}

?>
