<?php

require "../api.php";

function setRole($db, $id, $role) {
    $user = $db->getUser($id)->fetch();

    if ($_SESSION["badgeid"] == $id) {
        return ["code" => 0, "msg" => "You can't modify your own role"];
    } else if (!$user) {
        return ["code" => 0, "msg" => "User with ID $id not found"];
    }

    $db->setUserRole($id, $role);

    return ["code" => 1, "name" => $user["username"]];
}

if (!$isAdmin) {
    http_response_code(403);
    echo json_encode(["code" => 0, "msg" => "Unauthorized"]);
    exit();
}

switch ($action) {
    case "setSiteStatus":
        echo json_encode(["code" => 1, "val" => $db->setSiteStatus($_POST["status"])]);
        break;
    case "setDevmode":
        echo json_encode(["code" => 1, "val" => $db->setDevMode($_POST["status"])]);
        break;
    case "setAdmin":
        echo json_encode(setRole($db, $_POST["badgeid"], 3));
        break;
    case "setManager":
        echo json_encode(setRole($db, $_POST["badgeid"], 2));
        break;
    case "setLead":
        echo json_encode(setRole($db, $_POST["badgeid"], 1));
        break;
    case "setBanned":
        $db->setUserBan($_POST["badgeid"], $_POST["value"]);
        echo json_encode(["code" => 1]);
        break;
    case "getAdmins":
        echo json_encode(["code" => 1, "val" => $db->listUsers(role: 3)->fetchAll()]);
        break;
    case "getManagers":
        echo json_encode(["code" => 1, "val" => $db->listUsers(role: 2)->fetchAll()]);
        break;
    case "getLeads":
        echo json_encode(["code" => 1, "val" => $db->listUsers(role: 1)->fetchAll()]);
        break;
    case "getBanned":
        echo json_encode(["code" => 1, "val" => $db->listBans()->fetchAll()]);
        break;
    case "getDepts":
        $depts = [];
        foreach ($db->listDepartments() as $dept) $depts[$dept["id"]] = $dept;
        echo json_encode(["code" => 1, "val" => $depts]);
        break;
    case "getBonuses":
        echo json_encode(["code" => 1, "val" => $db->listBonuses()->fetchAll()]);
        break;
    case "getRewards":
        echo json_encode(["code" => 1, "val" => $db->listRewards(hidden: true)->fetchAll()]);
        break;
    case "addDept":
        echo json_encode(["code" => 1, "val" => $db->createDepartment($_POST["name"], $_POST["hidden"])]);
        break;
    case "addReward":
        echo json_encode(["code" => 1, "val" => $db->createReward($_POST["name"], $_POST["description"], $_POST["hours"], $_POST["hidden"])]);
        break;
    case "updateDept":
        echo json_encode(["code" => 1, "val" => $db->updateDepartment($_POST["id"], $_POST["name"], $_POST["hidden"])]);
        break;
    case "updateReward":
        echo json_encode(["code" => 1, "val" => $db->updateReward($_POST["id"], $_POST["field"], $_POST["value"])]);
        break;
    case "removeBonus":
        echo json_encode(["code" => 1, "val" => $db->deleteBonus($_POST["id"])]);
        break;
    case "addBonus":
        echo json_encode(["code" => 1, "val" => $db->createBonus($_POST["start"], $_POST["stop"], $_POST["depts"], $_POST["modifier"])]);
        break;
}

?>
