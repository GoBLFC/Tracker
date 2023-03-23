<?php

require "../api.php";

if (!($isManager || $isAdmin)) {
    http_response_code(403);
    echo error("Unauthorized");
    exit();
}

class Manager extends API {

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

    public function getDepts($params) {
        $depts = [];
        foreach ($this->db->listDepartments() as $dept) $depts[$dept["id"]] = $dept;
        return $depts;
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

    public function addTime($params) {
        return $this->db->createTime(
            $params["id"],
            $params["start"],
            $params["stop"],
            $params["dept"],
            $params["notes"],
            $this->badgeID
        );
    }

    public function removeTime($params) {
        $this->db->deleteTime($params["id"]);
        return $this->success("Time removed");
    }

    public function getRewardClaims($params) {
        return $this->db->listRewardClaims($params["id"])->fetchAll();
    }

    public function claimReward($params) {
        return $this->db->claimReward($params["uid"], $params["type"]);
    }

    public function unclaimReward($params) {
        return $this->db->unclaimReward($params["uid"], $params["type"]);
    }

}

$api = new Manager($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
