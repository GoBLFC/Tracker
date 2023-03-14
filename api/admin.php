<?php

header("Content-Type: application/json");

require "../main.php";

$ret['code'] = -1;
//$ret['msg'] = "Unknown Action.";

$action = $_POST['action'];

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

die(json_encode($ret));

?>
