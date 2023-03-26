<?php

require "../api.php";

$access = [
    "checkIn" => Role::Volunteer,
    "checkOut" => Role::Volunteer,
    "checkInUser" => Role::Manager,
    "checkOutUser" => Role::Manager,
    "addTime" => Role::Manager,
    "removeTime" => Role::Manager,
    "getClockTime" => Role::Volunteer,
    "getClockTimeUser" => Role::Manager,
    "getTimeToday" => Role::Volunteer,
    "getTimeTodayUser" => Role::Manager,
    "getEarnedTime" => Role::Volunteer,
    "getEarnedTimeUser" => Role::Manager,
    "getTimeEntries" => Role::Manager
];

class Tracker extends API {

    public function checkIn($dept) {
        /*

        Check yourself in.

        Parameters:
            - `dept` (int): Department ID to check into

        Returns:
            array("success" => (bool), "message" => (str))

        */

        if ($dept == "-1") {
            return $this->error("Invalid department specified");
        }

        $result = $this->db->checkIn($_SESSION["badgeid"], $dept);

        if (!$result) {
            return $this->error("Already checked in");
        }

        return $this->success("Checked in");
    }

    public function checkOut() {
        /*

        Check yourself out. ;)

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $result = $this->db->checkOut($_SESSION["badgeid"]);

        if (!$result->rowCount()) {
            return $this->error("Already checked out");
        }

        return $this->success("Checked out");
    }

    public function checkInUser($id, $dept, $start, $notes = null) {
        /*

        Check in another user.

        Parameters:
            - `id` (int): ID of the user to check in
            - `dept` (int): ID of the department to check the user into
            - `start` (str): Start time of the check in
            - `notes` (str) (optional): Notes to add about the check in

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $result = $this->db->checkIn($id, $dept, $start, $_SESSION["badgeid"], $notes);

        if (!$result) {
            return $this->error("Already checked in");
        }

        return $this->success("Checked in");
    }

    public function checkOutUser($id) {
        /*

        Check out another user.

        Parameters:
            - `id` (int): ID of the user to check out

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $result = $this->db->checkOut($id);

        if (!$result->rowCount()) {
            return $this->error("Already checked out");
        }

        return $this->success("Checked out");
    }

    public function addTime($id, $start, $stop, $dept, $notes = null) {
        /*

        Create a new time entry, start to finish, for another user.

        Parameters:
            - `id` (int): ID of the user to add time for
            - `start` (str): Start time for the entry
            - `stop` (str): Stop time for the entry
            - `dept` (int): Department ID to attribute the time to
            - `notes` (str) (optional): Notes to add about this entry

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->createTime($id, $start, $stop, $dept, $notes, $_SESSION["badgeid"]);
        return $this->success("Time added");
    }

    public function removeTime($id) {
        /*

        Delete a time entry.

        Parameters:
            - `id` (int): ID of the time entry to delete

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->deleteTime($id);
        return $this->success("Time removed");
    }

    public function getClockTime() {
        /*

        Get the amount of time (in seconds) since you've been checked in.

        Returns (on success):
            array("time" => (int))

        Returns (on failure):
            array("success" => (bool), "message" => (str))

        */

        $time = $this->db->getCheckIn($_SESSION["badgeid"])->fetch();

        if (!$time) {
            return $this->error("Not checked in");
        }

        $result = ["time" => UserTimeClock::calculateTimeTotal([$time])];

        return $result;
    }

    public function getClockTimeUser($id) {
        /*

        Get the amount of time (in seconds) since another user has been checked in.

        Parameters:
            - `id` (int): ID of the user

        Returns (on success):
            array("time" => (int))

        Returns (on failure):
            array("success" => (bool), "message" => (str))

        */

        $time = $this->db->getCheckIn($id)->fetch();

        if (!$time) {
            return $this->error("Not checked in");
        }

        $result = ["time" => UserTimeClock::calculateTimeTotal([$time])];

        return $result;
    }

    public function getTimeToday() {
        /*

        Get the amount of time (in seconds) you have been checked in today.

        Returns:
            array("time" => (int))

        */

        $times = $this->db->listTimes(uid: $_SESSION["badgeid"])->fetchAll();
        $timeTotal = UserTimeClock::calculateTimeSinceDay($times, new DateTime());
        return $timeTotal;
    }

    public function getTimeTodayUser($id) {
        /*

        Get the amount of time (in seconds) another user has been checked in today.

        Parameters:
            - `id` (int): ID of the user

        Returns:
            array("time" => (int))

        */

        $times = $this->db->listTimes(uid: $id)->fetchAll();
        $timeTotal = ["time" => UserTimeClock::calculateTimeSinceDay($times, new DateTime())];
        return $timeTotal;
    }

    public function getEarnedTime() {
        /*

        Get the total amount of time (in seconds) you have earned.
        In other words, the total amount of time volunteered with bonus modifiers applied.

        Returns:
            array("time" => (int))

        */

        $times = $this->db->listTimes(uid: $_SESSION["badgeid"])->fetchAll();
        $bonuses = $this->db->listBonuses()->fetchAll();
        $earnedTime = ["time" => UserTimeClock::calculateTimeTotal($times, $bonuses)];
        return $earnedTime;
    }

    public function getEarnedTimeUser($id) {
        /*

        Get the total amount of time (in seconds) another user has earned.
        In other words, the total amount of time volunteered with bonus modifiers applied.

        Parameters:
            - `id` (int): ID of the user

        Returns:
            array("time" => (int))

        */

        $times = $this->db->listTimes(uid: $id);
        $bonuses = $this->db->listBonuses()->fetchAll();
        $earnedTime = ["time" => UserTimeClock::calculateTimeTotal($times, $bonuses)];
        return $earnedTime;
    }

    public function getTimeEntries($id) {
        /*

        Get all time entries for a particular user.

        Parameters:
            - `id` (int): ID of the user

        Returns:
            An array of zero or more associative arrays, representing time entries. Entry row format:
            array(
                "id" => (int),
                "uid" => (int),
                "check_in" => (str),
                "check_out" => (str || null),
                "dept" => (int),
                "notes" => (str || null),
                "added_by" => (int),
                "auto" => (int)
            )

        */

        return $this->db->listTimes(uid: $id)->fetchAll();
    }

}

$api = new Tracker($db);
echo apiCall($api, $access, $role, $_POST);

?>
