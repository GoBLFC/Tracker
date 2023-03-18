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

    public function createUser($id, $firstName, $lastName, $username) {
        // Check for existing user first
        if ($this->getUser($id)->fetch()) {
            return null;
        }

        $sql = "INSERT INTO `users` (`id`, `first_name`, `last_name`, `nickname`, `tg_uid`) VALUES (:id, :firstName, :lastName, :username, :tgid)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":firstName", $firstName, PDO::PARAM_STR);
        $stmt->bindValue(":lastName", $lastName, PDO::PARAM_STR);
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        $stmt->bindValue(":tgid", bin2hex(random_bytes(16)), PDO::PARAM_STR);
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

    public function listUsers() {
        $stmt = $this->conn->query("SELECT * FROM `users`");
        return $stmt;
    }

    public function listUsersByRole($admin = null, $manager = null, $lead = null) {
        // TODO: Consolidate with `listUsers` function

        $sql = "SELECT * FROM `users` WHERE ";

        $roles = [];
        if (isset($admin)) { $roles[] = "`admin` = :admin"; }
        if (isset($manager)) { $roles[] = "`manager` = :manager"; };
        if (isset($lead)) { $roles[] = "`lead` = :lead"; }

        // No roles were set, so list all users
        if (!$roles) {
            return $this->listUsers();
        }

        $sql .= implode(" AND ", $roles);

        $stmt = $this->conn->prepare($sql);
        if (isset($admin)) { $stmt->bindValue(":admin", $admin, PDO::PARAM_INT); }
        if (isset($manager)) { $stmt->bindValue(":manager", $manager, PDO::PARAM_INT); }
        if (isset($lead)) { $stmt->bindValue(":lead", $lead, PDO::PARAM_INT); }
        $stmt->execute();

        return $stmt;
    }

    public function getUserRole($id) {
        $sql = "SELECT `admin`, `manager`, `lead` FROM `users` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function setUserRole($id, $admin = null, $manager = null, $lead = null) {
        $sql = "UPDATE `users` SET ";

        $roles = [];
        if (isset($admin)) { $roles[] = "`admin` = :admin"; }
        if (isset($manager)) { $roles[] = "`manager` = :manager"; };
        if (isset($lead)) { $roles[] = "`lead` = :lead"; }

        // No roles were set
        if (!$roles) {
            return;
        }

        $sql .= implode(", ", $roles);
        $sql .= " WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        if (isset($admin)) { $stmt->bindValue(":admin", $admin, PDO::PARAM_INT); }
        if (isset($manager)) { $stmt->bindValue(":manager", $manager, PDO::PARAM_INT); }
        if (isset($lead)) { $stmt->bindValue(":lead", $lead, PDO::PARAM_INT); }
        $stmt->execute();

        return $stmt;
    }

    public function setUserBan($id, $banned) {
        // TODO: Enable bans for non-existent users
        $sql = "UPDATE `users` SET `banned` = :banned WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":banned", $banned, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getUserBan($id) {
        $sql = "SELECT `banned` FROM `users` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $ban = $stmt->fetch();

        if (!$ban) { return false; }

        return $ban[0];
    }

    public function listBans() {
        $stmt = $this->conn->query("SELECT * FROM `users` WHERE `banned` = 1");
        return $stmt;
    }

    public function searchUsers($input) {
        $sql = "SELECT * FROM `users` WHERE `id` = :id OR `nickname` LIKE :nickname OR CONCAT(first_name, ' ', last_name) LIKE :inputname LIMIT 20";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $input, PDO::PARAM_STR);
        $stmt->bindValue(":nickname", "%$input%", PDO::PARAM_STR);
        $stmt->bindValue(":inputname", "%$input%", PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // ---

    public function getUserByTGCID($id) {
        $sql = "SELECT * FROM `users` WHERE `tg_chatid` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getUserByTGUID($id) {
        $sql = "SELECT * FROM `users` WHERE `tg_uid` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    public function getTGUID($id) {
        $sql = "SELECT `tg_uid` FROM `users` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch()[0];
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

        $sql = "INSERT INTO `tracker` (`uid`, `checkin`, `dept`, `notes`, `addedby`) VALUES (:uid, :checkin, :dept, :notes, :uid2)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":checkin", date("Y-m-d H:i:s", strtotime($start)), PDO::PARAM_STR);
        $stmt->bindValue(":notes", $notes, PDO::PARAM_STR);
        $stmt->bindValue(":uid2", $addedBy, PDO::PARAM_INT);
        $stmt->bindValue(":dept", $dept, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function checkOut($uid, $autoTime) {
        $sql = "UPDATE `tracker` SET `checkout` = :time, `auto` = :auto WHERE `uid` = :uid AND `checkout` IS NULL ORDER BY `id` DESC LIMIT 1";

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
        $sql = "SELECT `id`, `dept`, `checkin` FROM `tracker` WHERE `uid` = :uid AND `checkout` IS NULL ORDER BY `id` DESC LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function createTime($id, $in, $out, $dept, $notes, $badgeID) {
        $sql = "INSERT INTO `tracker` (`uid`, `checkin`, `checkout`, `dept`, `notes`, `addedby`) VALUES (:uid, :checkin, :checkout, :dept, :notes, :addedby)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $id, PDO::PARAM_INT);
        $stmt->bindValue(":checkin", $in, PDO::PARAM_STR);
        $stmt->bindValue(":checkout", $out, PDO::PARAM_STR);
        $stmt->bindValue(":dept", $dept, PDO::PARAM_INT);
        $stmt->bindValue(":notes", $notes, PDO::PARAM_STR);
        $stmt->bindValue(":addedby", $badgeID, PDO::PARAM_INT);
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
        $sql = "SELECT * FROM `tracker` WHERE checkout IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt;
    }

    public function getAllTrackerEntries() {
        $sql = "SELECT *, TIME_TO_SEC(TIMEDIFF(`checkout`, `checkin`)) as diff FROM `tracker` WHERE checkout IS NOT NULL AND checkin IS NOT NULL";
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

    public function createReward($name, $desc, $hours, $type, $hidden = 0) {
        $sql = "INSERT INTO `rewards` (`name`, `desc`, `hours`, `type`, `hidden`) VALUES (:name, :desc, :hours, :type, :hidden)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":desc", $desc, PDO::PARAM_STR);
        $stmt->bindValue(":hours", $hours, PDO::PARAM_INT);
        $stmt->bindValue(":type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":hidden", $hidden, PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function updateReward($id, $field, $value, $type) {
        $sql = "UPDATE `rewards` SET ";

        $fields = ["name", "desc", "hours", "hidden"];

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

    public function listRewards($hidden = 0) {
        $sql = "SELECT * FROM `rewards`";

        if (!$hidden) { $sql .= " WHERE `hidden` = 0"; }

        $stmt = $this->conn->query($sql);

        return $stmt;
    }

    // ############
    // ## Claims ##
    // ############

    public function claimReward($uid, $claim) {
        $sql = "INSERT INTO `claims` (`uid`, `claim`, `date`) VALUES (:uid, :claim, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":claim", $claim, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
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
        $sql = "SELECT `claim`, `date` FROM `claims` WHERE `uid` = :uid";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // #############
    // ## Bonuses ##
    // #############

    public function createBonus($start, $stop, $depts, $modifier) {
        $sql = "INSERT INTO `time_mod` (`id`, `start`, `stop`, `dept`, `modifier`, `hidden`) VALUES (NULL, :start, :stop, :depts, :mod, 0)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":start", $start, PDO::PARAM_STR);
        $stmt->bindValue(":stop", $stop, PDO::PARAM_STR);
        $stmt->bindValue(":depts", $depts, PDO::PARAM_STR);
        $stmt->bindValue(":mod", $modifier, PDO::PARAM_STR);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deleteBonus($id) {
        $sql = "DELETE FROM `time_mod` WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function listBonuses($hidden = 0) {
        $sql = "SELECT * FROM `time_mod` WHERE `hidden` = :hidden";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":hidden", $hidden);
        $stmt->execute();

        return $stmt;
    }

    // ###################
    // ## Notifications ##
    // ###################

    public function createNotification($uid, $type, $reward, $message, $ack) {
        $sql = "INSERT INTO `notifications` (`uid`, `type`, `reward`, `message`, `ack`) VALUES (:uid, :type, :reward, :message, :ack)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":reward", $reward, PDO::PARAM_INT);
        $stmt->bindValue(":message", $message, PDO::PARAM_STR);
        $stmt->bindValue(":ack", $ack, PDO::PARAM_INT);
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
        $sql = "UPDATE `notifications` SET `hasread` = '1' WHERE `id` = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function listNotifications($uid, $ack, $hasRead = 0) {
        $sql = "SELECT * FROM `notifications` WHERE `uid` = :uid AND `hasread` = :hasRead AND `ack` = :ack";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->bindValue(":hasRead", $hasRead, PDO::PARAM_INT);
        $stmt->bindValue(":ack", $ack, PDO::PARAM_INT);
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
        $sql = "UPDATE `notifications` SET `hasread` = 1, `ack` = 1 WHERE `uid` = :uid AND `hasread` = 0 AND `ack` = 1";

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
        $sql = "INSERT INTO `kiosks` (`session`, `authorized`) VALUES (:session, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":session", $session, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
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
        $sql = "UPDATE `users` SET `tg_quickcode` = :quickcode, `tg_quickcodetime` = NOW() WHERE `id` = :uid";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":quickcode", $code, PDO::PARAM_INT);
        $stmt->bindValue(":uid", $uid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function checkQuickCode($code) {
        $sql = "SELECT * FROM `users` WHERE `tg_quickcode` = :quickcode AND `tg_quickcodetime` BETWEEN NOW() - INTERVAL 30 SECOND AND NOW()";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":quickcode", intval($code), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function updateTGChat($chatID, $tguid) {
        // Update chat ID and invalidate UID
        $sql = "UPDATE `users` SET `tg_chatid` = :chatID, `tg_uid` = :newID WHERE `tg_uid` = :tgUID";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":chatID", $chatID, PDO::PARAM_INT);
        $stmt->bindValue(":tgUID", $tguid, PDO::PARAM_STR);
        $stmt->bindValue(":newID", bin2hex(random_bytes(16)), PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    // ####################
    // ## Site Functions ##
    // ####################

    public function getDevMode() {
        $stmt = $this->conn->query("SELECT `devmode` FROM `settings`");
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

    public function setDevMode($devmode) {
        $sql = "UPDATE `settings` SET `devmode` = :devmode";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":devmode", $devmode, PDO::PARAM_INT);
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
