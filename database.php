<?php

class Database {

    public $conn;

    public function __construct($host, $name, $username, $password) {
        $this->conn = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $username, $password,
            [PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    // ###########
    // ## Users ##
    // ###########

    public function createUser($id, $username, $firstName, $lastName) {
        // Check for existing user first
        if ($this->getUser($id)->fetch()) {
            return null;
        }

        $sql = "INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `tg_setup_code`) VALUES (:id, :username, :firstName, :lastName, :tg_setup_code)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        $stmt->bindValue(":firstName", $firstName, PDO::PARAM_STR);
        $stmt->bindValue(":lastName", $lastName, PDO::PARAM_STR);
        $stmt->bindValue(":tg_setup_code", bin2hex(random_bytes(16)), PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function getUser($id) {
        $sql = "SELECT * FROM `users` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function listUsers($role = null) {
        $sql = "SELECT * FROM `users`";

        if ($role) { $sql .= "WHERE `role` = :role"; }

        $stmt = $this->conn->prepare($sql);
        if ($role) { $stmt->bindParam(":role", $role, PDO::PARAM_INT); }
        $stmt->execute();

        return $stmt;
    }

    public function getUserRole($id) {
        $sql = "SELECT `role` FROM `users` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function setUserRole($id, $role) {
        $sql = "UPDATE `users` SET `role` = :role WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":role", $role, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function setUserBan($id, $banned) {
        $sql = "UPDATE `users` SET `role` = :banned WHERE `id` = :id";

        // Role value of -1 means the user is banned
        // Otherwise set their role back to regular volunteer status
        $banned = $banned ? -1 : 0;

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":banned", $banned, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getUserBan($id) {
        $sql = "SELECT `role` FROM `users` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $ban = $stmt->fetch();

        // User exists and they have the "ban" role
        if ($ban && $ban[0] == -1) { return true; }

        return false;
    }

    public function listBans() {
        $stmt = $this->conn->query("SELECT * FROM `users` WHERE `role` = -1");
        return $stmt;
    }

    public function searchUsers($input) {
        $sql = "SELECT * FROM `users` WHERE `id` = :id OR `username` LIKE :username OR CONCAT(first_name, ' ', last_name) LIKE :inputname LIMIT 20";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $input, PDO::PARAM_STR);
        $stmt->bindValue(":username", "%$input%", PDO::PARAM_STR);
        $stmt->bindValue(":inputname", "%$input%", PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // ---

    public function getUserByTelegramID($id) {
        $sql = "SELECT * FROM `users` WHERE `tg_uid` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getUserBySetupCode($code) {
        $sql = "SELECT * FROM `users` WHERE `tg_setup_code` = :code";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":code", $code, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // #################
    // ## Departments ##
    // #################

    public function createDepartment($name, $hidden = 0) {
        $sql = "INSERT INTO `departments` (`name`, `hidden`) VALUES (:name, :hidden)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":hidden", $hidden, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function updateDepartment($id, $name, $hidden = null) {
        $sql = "UPDATE `departments` SET `name` = :name";

        if (isset($hidden)) { $sql .= ", `hidden` = :hidden"; }

        $sql .= " WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        if (isset($hidden)) { $stmt->bindValue(":hidden", $hidden, PDO::PARAM_INT); }
        $stmt->execute();

        return $stmt;
    }

    public function listDepartments($hidden = 0) {
        $sql = "SELECT * FROM `departments`";

        if (!$hidden) { $sql .= " WHERE `hidden` = 0"; }

        $stmt = $this->conn->query($sql);

        return $stmt;
    }

    // ################
    // ## Time Clock ##
    // ################

    public function checkIn($uid, $dept, $notes, $addedBy, $start = "now") {
        // Check for existing check-in first
        if ($this->getCheckIn($uid)->fetch()) {
            return null;
        }

        $sql = "INSERT INTO `tracker` (`uid`, `check_in`, `dept`, `notes`, `added_by`) VALUES (:uid, :checkIn, :dept, :notes, :addedBy)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":checkIn", date("Y-m-d H:i:s", strtotime($start)), PDO::PARAM_STR);
        $stmt->bindValue(":notes", $notes, PDO::PARAM_STR);
        $stmt->bindValue(":addedBy", $addedBy, PDO::PARAM_INT);
        $stmt->bindValue(":dept", $dept, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function checkOut($uid, $autoTime) {
        $sql = "UPDATE `tracker` SET `check_out` = :time, `auto` = :auto WHERE `uid` = :uid AND `check_out` IS NULL ORDER BY `id` DESC LIMIT 1";

        $time = date("Y-m-d H:i:s");
        if ($autoTime) $time = $autoTime->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":time", $time, PDO::PARAM_STR);
        $stmt->bindValue(":auto", isset($autoTime), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getCheckIn($uid) {
        $sql = "SELECT `id`, `dept`, `check_in` FROM `tracker` WHERE `uid` = :uid AND `check_out` IS NULL ORDER BY `id` DESC LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function createTime($id, $checkIn, $checkOut, $dept, $notes, $addedBy) {
        $sql = "INSERT INTO `tracker` (`uid`, `check_in`, `check_out`, `dept`, `notes`, `added_by`) VALUES (:uid, :checkIn, :checkOut, :dept, :notes, :addedBy)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $id, PDO::PARAM_INT);
        $stmt->bindValue(":checkIn", $checkIn, PDO::PARAM_STR);
        $stmt->bindValue(":checkOut", $checkOut, PDO::PARAM_STR);
        $stmt->bindValue(":dept", $dept, PDO::PARAM_INT);
        $stmt->bindValue(":notes", $notes, PDO::PARAM_STR);
        $stmt->bindValue(":addedBy", $addedBy, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deleteTime($id) {
        $sql = "DELETE FROM `tracker` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // ---

    public function getActiveCheckIns() {
        $sql = "SELECT * FROM `tracker` WHERE check_out IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt;
    }

    public function getAllTrackerEntries() {
        $sql = "SELECT *, TIME_TO_SEC(TIMEDIFF(`check_out`, `check_in`)) as diff FROM `tracker` WHERE check_out IS NOT NULL AND check_in IS NOT NULL";
        $stmt = $this->conn->query($sql);
        return $stmt;
    }

    public function listUnclockedUsers() {
        $stmt = $this->conn->query("SELECT * FROM `tracker` WHERE `auto` = 1");
        return $stmt;
    }

    // #############
    // ## Rewards ##
    // #############

    public function createReward($name, $desc, $hours) {
        $sql = "INSERT INTO `rewards` (`name`, `desc`, `hours`) VALUES (:name, :desc, :hours)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":desc", $desc, PDO::PARAM_STR);
        $stmt->bindValue(":hours", $hours, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function updateReward($id, $field, $value) {
        $sql = "UPDATE `rewards` SET ";

        $fields = ["name", "desc", "hours"];

        // Not a valid field, abort
        if (!in_array($field, $fields)) { return; };

        foreach ($fields as $fieldName) {
            if ($field == $fieldName) {
                $sql .= "`$field` = :value";
            }
        }

        $sql .= " WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":value", $value, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    public function listRewards() {
        $stmt = $this->conn->query("SELECT * FROM `rewards`");
        return $stmt;
    }

    // ############
    // ## Claims ##
    // ############

    public function claimReward($uid, $claim) {
        $sql = "INSERT INTO `claims` (`uid`, `claim`) VALUES (:uid, :claim)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":claim", $claim, PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function unclaimReward($uid, $claim) {
        $sql = "DELETE FROM `claims` WHERE `uid` = :uid AND `claim` = :claim";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":claim", $claim, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    public function listRewardClaims($uid) {
        $sql = "SELECT `claim` FROM `claims` WHERE `uid` = :uid";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // #############
    // ## Bonuses ##
    // #############

    public function createBonus($start, $stop, $depts, $modifier) {
        $sql = "INSERT INTO `bonuses` (`start`, `stop`, `dept`, `modifier`) VALUES (:start, :stop, :depts, :mod)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":start", $start, PDO::PARAM_STR);
        $stmt->bindValue(":stop", $stop, PDO::PARAM_STR);
        $stmt->bindValue(":depts", $depts, PDO::PARAM_STR);
        $stmt->bindValue(":mod", $modifier, PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deleteBonus($id) {
        $sql = "DELETE FROM `bonuses` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function listBonuses() {
        $stmt = $this->conn->query("SELECT * FROM `bonuses`");
        return $stmt;
    }

    // ###################
    // ## Notifications ##
    // ###################

    public function createNotification($uid, $type, $reward, $message) {
        $sql = "INSERT INTO `notifications` (`uid`, `type`, `reward`, `message`) VALUES (:uid, :type, :reward, :message)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":reward", $reward, PDO::PARAM_INT);
        $stmt->bindValue(":message", $message, PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deleteNotification($uid, $reward) {
        $sql = "DELETE FROM `notifications` WHERE `reward` = :reward AND `uid` = :uid";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":reward", $reward, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function markNotificationRead($id) {
        $sql = "UPDATE `notifications` SET `has_read` = '1' WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function listNotifications($uid, $hasRead = 0) {
        $sql = "SELECT * FROM `notifications` WHERE `uid` = :uid AND `has_read` = :hasRead";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":hasRead", $hasRead, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function hasBeenNotified($uid, $reward) {
        $sql = "SELECT * FROM `notifications` WHERE `uid` = :uid AND `reward` = :reward";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":reward", $reward, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    public function ackAllNotifs($uid) {
        $sql = "UPDATE `notifications` SET `has_read` = 1 WHERE `uid` = :uid AND `has_read` = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // ############
    // ## Kiosks ##
    // ############

    public function checkKiosk($session) {
        $sql = "SELECT * FROM `kiosks` WHERE `session` = :session";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":session", $session, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    public function authorizeKiosk($session) {
        $sql = "INSERT INTO `kiosks` (`session`) VALUES (:session)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":session", $session, PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deauthorizeKiosk($session) {
        $sql = "DELETE FROM `kiosks` WHERE `session` = :session";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":session", $session, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // ##########################
    // ## Telegram Integration ##
    // ##########################

    public function setQuickCode($uid, $code) {
        $sql = "INSERT INTO `telegram` (`code`, `uid`) VALUES (:code, :uid)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":code", $code, PDO::PARAM_STR);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function checkQuickCode($code) {
        $sql = "SELECT `uid` FROM `telegram` WHERE `code` = :code AND `time` > NOW() - INTERVAL 30 SECOND";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":code", $code, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    public function updateTGChat($id, $code) {
        // Update chat ID and invalidate UID
        $sql = "UPDATE `users` SET `tg_uid` = :id, `tg_setup_code` = :newCode WHERE `tg_setup_code` = :code";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":code", $code, PDO::PARAM_STR);
        $stmt->bindValue(":newCode", bin2hex(random_bytes(16)), PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // ####################
    // ## Site Functions ##
    // ####################

    public function getDevMode() {
        $stmt = $this->conn->query("SELECT `dev_mode` FROM `settings`");
        return $stmt->fetch()[0];
    }

    public function getSiteStatus() {
        $stmt = $this->conn->query("SELECT `site_status` FROM `settings`");
        return $stmt->fetch()[0];
    }

    public function setSiteStatus($status) {
        $sql = "UPDATE `settings` SET `site_status` = :status";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":status", $status, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function setDevMode($devMode) {
        $sql = "UPDATE `settings` SET `dev_mode` = :devMode";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":devMode", $devMode, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // #############
    // ## General ##
    // #############

    public function createLog($uid, $action, $data) {
        $sql = "INSERT INTO `logs` (`uid`, `action`, `data`) VALUES (:uid, :action, :data)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":action", $action, PDO::PARAM_STR);
        $stmt->bindValue(":data", $data, PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function getLogs() {
        $stmt = $this->conn->query("SELECT * FROM `logs`");
        return $stmt;
    }

}

?>
