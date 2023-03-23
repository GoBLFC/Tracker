<?php

require "../api.php";

class General extends API {

    public function checkIn($params) {
        $dept = $params["dept"];

        if ($dept == "-1") {
            return $this->error("Invalid department specified");
        }

        $result = $this->db->checkIn($this->badgeID, $dept, "", $this->badgeID);

        if (!$result) {
            return $this->error("Already checked in");
        }

        return $this->success("Checked in");
    }

    public function checkOut($params) {
        $result = $this->db->checkOut($this->badgeID, null);

        if (!$result->rowCount()) {
            return $this->error("Already checked out");
        }

        return $this->success("Checked out");
    }

    public function getClockTime($params) {
        $time = $this->db->getCheckIn($this->badgeID)->fetch();

        // Not clocked in
        if (!$time) {
            return $this->error("Not clocked in");
        }

        $result = UserTimeClock::calculateTimeTotal([$time]);

        return $result;
    }

    public function getMinutesToday($params) {
        $times = $this->db->listTimes(uid: $this->badgeID)->fetchAll();
        $timeTotal = UserTimeClock::calculateTimeSinceDay($times, new DateTime());

        return $timeTotal;
    }

    public function getEarnedTime($params) {
        $times = $this->db->listTimes(uid: $this->badgeID)->fetchAll();
        $bonuses = $this->db->listBonuses()->fetchAll();
        $earnedTime = UserTimeClock::calculateTimeTotal($times, $bonuses);

        return $earnedTime;
    }

    public function getNotifications($params) {
        return $this->db->listNotifications($this->badgeID, 0)->fetchAll();
    }

    public function readNotification($params) {
        return $this->db->markNotificationRead($params["id"]);
    }

    public function ackAllNotifs($params) {
        return $this->db->ackAllNotifs($this->badgeID);
    }

}

$api = new General($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
