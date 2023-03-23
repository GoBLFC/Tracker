<?php

require "../api.php";

if (!$isAdmin) {
    http_response_code(403);
    echo error("Unauthorized");
    exit();
}

function setRole($db, $id, $role) {
    $user = $db->getUser($id)->fetch();

    if ($_SESSION["badgeid"] == $id) {
        return error("You can't modify your own role");
    } else if (!$user) {
        return error("User with ID $id not found");
    }

    $db->setUserRole($id, $role);

    return $user["username"];
}

class Admin extends API {

    public function setSiteStatus($params) {
        return $this->db->setSiteStatus($params["status"]);
    }

    public function setDevMode($params) {
        return $this->db->setDevMode($params["status"]);
    }

    public function setAdmin($params) {
        echo json_encode(setRole($this->db, $params["badgeid"], 3));
    }

    public function setManager($params) {
        echo json_encode(setRole($this->db, $params["badgeid"], 2));
    }

    public function setLead($params) {
        echo json_encode(setRole($this->db, $params["badgeid"], 1));
    }

    public function setBanned($params) {
        $this->db->setUserBan($params["badgeid"], $params["value"]);
        return $this->success("Updated ban");
    }

    public function getAdmins($params) {
        return $this->db->listUsers(role: 3)->fetchAll();
    }

    public function getManagers($params) {
        return $this->db->listUsers(role: 2)->fetchAll();
    }

    public function getLeads($params) {
        return $this->db->listUsers(role: 1)->fetchAll();
    }

    public function getBanned($params) {
        return $this->db->listBans()->fetchAll();
    }

    public function getDepts($params) {
        $depts = [];
        foreach ($this->db->listDepartments() as $dept) $depts[$dept["id"]] = $dept;
        return $depts;
    }

    public function getBonuses($params) {
        return $this->db->listBonuses()->fetchAll();
    }

    public function getRewards($params) {
        return $this->db->listRewards()->fetchAll();
    }

    public function addDept($params) {
        return $this->db->createDepartment($params["name"], $params["hidden"]);
    }

    public function addReward($params) {
        return $this->db->createReward($params["name"], $params["description"], $params["hours"], $params["hidden"]);
    }

    public function updateDept($params) {
        return $this->db->updateDepartment($params["id"], $params["name"], $params["hidden"]);
    }

    public function updateReward($params) {
        return $this->db->updateReward($params["id"], $params["field"], $params["value"]);
    }

    public function removeBonus($params) {
        return $this->db->deleteBonus($params["id"]);
    }

    public function addBonus($params) {
        return $this->db->createBonus($params["start"], $params["stop"], $params["depts"], $params["modifier"]);
    }

}

$api = new Admin($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
