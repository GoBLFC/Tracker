<?php

require "../api.php";

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

class User extends API {

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

    public function getUser($params) {
        $id = $params["id"];

        $user = $this->db->getUser($id)->fetch();
        $dept = $this->db->getCheckIn($id)->fetch();
        $user["dept"] = $dept ? $dept : null;
        return $user;
    }

    public function getClockTimeOther($params) {
        $time = $this->db->getCheckIn($params["id"])->fetch();

        // Not clocked in
        if (!$time) {
            return $this->error("Not clocked in");
        }

        $result = UserTimeClock::calculateTimeTotal([$time]);

        return $result;
    }

    public function getMinutesTodayOther($params) {
        $times = $this->db->listTimes(uid: $params["id"])->fetchAll();
        $timeTotal = UserTimeClock::calculateTimeSinceDay($times, new DateTime());
        return $timeTotal;
    }

    public function getEarnedTimeOther($params) {
        $times = $this->db->listTimes(uid: $params["id"]);
        $bonuses = $this->db->listBonuses();
        $earnedTime = UserTimeClock::calculateTimeTotal($times, $bonuses);

        return $earnedTime;
    }

    public function getTimeEntriesOther($params) {
        return $this->db->listTimes(uid: $params["id"])->fetchAll();
    }

    public function checkOutOther($params) {
        $result = $this->db->checkOut($params["id"], null);

        if (!$result->rowCount()) {
            return $this->error("Already checked out");
        }

        return $this->success("Checked out");
    }

    public function checkInOther($params) {
        $result = $this->db->checkIn($params["id"], $params["dept"], $params["notes"], $this->badgeID, $params["start"]);

        if (!$result) {
            return $this->error("Already checked in");
        }

        return $this->success("Checked in");
    }

    public function createUser($params) {
        $result = $this->db->createUser($params["badgeID"]);

        if (!$result) {
            return $this->error("User already exists");
        }

        return $this->success("User created");
    }

    public function getUserSearch($params) {
        $users = $this->db->searchUsers($params["input"]);
        $results = [];

        foreach ($users as $user) {
            $dept = $this->db->getCheckIn($user["id"])->fetch();
            if ($dept) $user["dept"] = $dept;
            $results[] = $user;
        }

        return $results;
    }

}

$api = new User($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
