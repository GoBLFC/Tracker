<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/21/2018
 * Time: 4:29 PM
 */

function isValidSession($session, $badge)
{
    if ($badge == "") return false;
    $user = getUser($badge, $session);
    if ($user == null) return false;
    if ($user[0]['id'] != $badge) return false;
    return $user;
}

// TODO: Return method specific results instead of arrays

/* SQL Queries */
function validateUser($id, $lName)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE id = :id AND last_name = :lname");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':lname', $lName, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetchAll(PDO::FETCH_ASSOC) == null) return false;
    return true;
}

function logoutSession($session)
{
    if (empty($session)) return;

    global $db;
    $stmt = $db->prepare("UPDATE `users` SET `last_session` = '' WHERE `users`.`last_session` = :lastsession");
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->execute();
}

function getUser($id, $session)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE id = :id AND last_session = :lastsession");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsers()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users`");
    $stmt->execute();

    $users = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) $users[$user['id']] = $user;

    return $users;
}

function getUserByID($id, $full)
{
    global $db;
    if ($full) {
        $stmt = $db->prepare("SELECT * FROM `users` WHERE id = :id");
    } else {
        $stmt = $db->prepare("SELECT `id`, `first_name`, `last_name`, `nickname` FROM `users` WHERE id = :id");
    }

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserBySession($session)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE last_session = :lastsession");
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserSessionCount($session)
{
    global $db;
    $stmt = $db->prepare("SELECT COUNT('id') as 'count' FROM `users` WHERE last_session = :lastsession");
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['count'];
}

function getActiveClockins()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `tracker` WHERE checkout IS NULL");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLogs()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `logs`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepartments($hidden)
{
    global $db;
    if (!$hidden) {
        $stmt = $db->prepare("SELECT * FROM `departments` WHERE `hidden` = 0");
    } else {
        $stmt = $db->prepare("SELECT * FROM `departments`");
    }

    $stmt->execute();

    $departments = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $dept) $departments[$dept['id']] = $dept;

    return $departments;
}

//Insert or update - excessive binds are because you can't re-use values, silly.
function updateSession($id, $fName, $lName, $nName, $session)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `users` (`id`, `first_name`, `last_name`, `nickname`, `admin`, `manager`, `last_session`, `last_ip`, `registered`, `reg_ua`) VALUES (:id, :fName, :lName, :nName, 0, 0, :lastsession, :lastip, NOW(), :regua) ON DUPLICATE KEY UPDATE `first_name` = :fName2, `last_name` = :lName2, `nickname` = :nName2, `last_session` = :lastsession2, `last_ip` = :lastip2, `last_login` = NOW()");
    //$stmt = $db->prepare("UPDATE `users` SET `last_session` = :lastsession, `last_ip` = :lastip WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':fName', $fName, PDO::PARAM_STR);
    $stmt->bindValue(':fName2', $fName, PDO::PARAM_STR);
    $stmt->bindValue(':lName', $lName, PDO::PARAM_STR);
    $stmt->bindValue(':lName2', $lName, PDO::PARAM_STR);
    $stmt->bindValue(':nName', $nName, PDO::PARAM_STR);
    $stmt->bindValue(':nName2', $nName, PDO::PARAM_STR);
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->bindValue(':lastsession2', $session, PDO::PARAM_STR);
    $stmt->bindValue(':regua', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
    $stmt->bindValue(':lastip', $_SERVER["HTTP_CF_CONNECTING_IP"], PDO::PARAM_STR);
    $stmt->bindValue(':lastip2', $_SERVER["HTTP_CF_CONNECTING_IP"], PDO::PARAM_STR);
    $stmt->execute();
}

function checkKiosk($session)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `kiosks` WHERE session = :sess");
    $stmt->bindValue(':sess', $session, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function authorizeKiosk($session)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `kiosks` (`session`, `authorized`) VALUES (:sess, CURRENT_TIMESTAMP)");
    $stmt->bindValue(':sess', $session, PDO::PARAM_STR);
    $stmt->execute();
}

function deauthorizeKiosk($session)
{
    global $db;
    $stmt = $db->prepare("DELETE FROM `kiosks` WHERE `session` = :sess");
    $stmt->bindValue(':sess', $session, PDO::PARAM_STR);
    $stmt->execute();
}

function setBanned($badgeID, $state)
{
    $user = getUserByID($badgeID, false);
    if ($state == 1 && !isset($user[0])) {
        updateSession($badgeID, "BAN", "BAN", "BAN", "BAN");
    }

    global $db;
    $stmt = $db->prepare("UPDATE `users` SET `banned` = :state WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $badgeID, PDO::PARAM_INT);
    $stmt->bindValue(':state', $state, PDO::PARAM_INT);
    $stmt->execute();

    return isset($user[0]) ? $user[0]['nickname'] : "Unknown";
}

function setAdmin($value, $badgeID)
{
    global $db;
    $stmt = $db->prepare("UPDATE `users` SET `admin` = :value WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $badgeID, PDO::PARAM_INT);
    $stmt->bindValue(':value', $value, PDO::PARAM_INT);
    $stmt->execute();
}

function setManager($value, $badgeID)
{
    global $db;
    $stmt = $db->prepare("UPDATE `users` SET `manager` = :value WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $badgeID, PDO::PARAM_INT);
    $stmt->bindValue(':value', $value, PDO::PARAM_INT);
    $stmt->execute();
}

function addDept($name, $hidden)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `departments` (`id`, `name`, `hidden`) VALUES (NULL, :name, :hidden);");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':hidden', $hidden, PDO::PARAM_INT);
    $stmt->execute();

    return $db->lastInsertId();
}

function updateDept($id, $name, $hidden)
{
    global $db;
    $stmt = $db->prepare("UPDATE `departments` SET `name` = :name, `hidden` = :hidden WHERE `departments`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':hidden', $hidden, PDO::PARAM_INT);
    $stmt->execute();

    return $db->lastInsertId();
}

function addBonus($start, $stop, $depts, $modifier)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `time_mod` (`id`, `start`, `stop`, `dept`, `modifier`) VALUES (NULL, :start, :stop, :depts, :mod)");
    $stmt->bindValue(':start', $start, PDO::PARAM_STR);
    $stmt->bindValue(':stop', $stop, PDO::PARAM_STR);
    $stmt->bindValue(':depts', $depts, PDO::PARAM_STR);
    $stmt->bindValue(':mod', $modifier, PDO::PARAM_STR);
    $stmt->execute();

    return $db->lastInsertId();
}

function removeBonus($id)
{
    global $db;
    $stmt = $db->prepare("DELETE FROM `time_mod` WHERE `id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function getAdmins()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE `admin` = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getManagers()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE `manager` = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBanned()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE `banned` = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepts()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `departments`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBonuses()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `time_mod`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNotifications($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `notifications` WHERE `uid` = :uid AND `hasread` = 0");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRewardClaims($uid, $type)
{
    global $db;
    $stmt = $db->prepare("SELECT `claim`, `date` FROM `claims` WHERE `uid` = :uid AND `claim` LIKE :type1");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':type1', "%" . $type . "%", PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function claimReward($uid, $type)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `claims` (`id`, `uid`, `claim`, `date`) VALUES (NULL, :uid, :type1, CURRENT_TIMESTAMP);");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':type1', $type, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function unclaimReward($uid, $type)
{
    global $db;
    $stmt = $db->prepare("DELETE FROM `claims` WHERE `claims`.`uid` = :uid AND `claim` = :type1");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':type1', $type, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationRead($id)
{
    global $db;
    $stmt = $db->prepare("UPDATE `notifications` SET `hasread` = '1' WHERE `notifications`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createNotification($uid, $type, $msg)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `notifications` (`uid`, `type`, `message`) VALUES (:uid, :type, :msg)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':msg', $msg, PDO::PARAM_STR);
    $stmt->execute();
}

function addTime($id, $in, $out, $dept, $notes, $badgeID)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `tracker` (`uid`, `checkin`, `checkout`, `dept`, `notes`, `addedby`) VALUES (:uid, :checkin, :checkout, :dept, :notes, :addedby)");
    $stmt->bindValue(':uid', $id, PDO::PARAM_INT);
    $stmt->bindValue(':checkin', $in, PDO::PARAM_STR);
    $stmt->bindValue(':checkout', $out, PDO::PARAM_STR);
    $stmt->bindValue(':dept', $dept, PDO::PARAM_INT);
    $stmt->bindValue(':notes', escapeText(filter_var($notes, FILTER_SANITIZE_STRING)), PDO::PARAM_STR);
    $stmt->bindValue(':addedby', $badgeID, PDO::PARAM_INT);
    $stmt->execute();

    return $db->lastInsertId();
}

function removeTime($id)
{
    global $db;
    $stmt = $db->prepare("DELETE FROM `tracker` WHERE `tracker`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function checkIn($uid, $dept, $notes, $addedBy)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `tracker` (`uid`, `checkin`, `dept`, `notes`, `addedby`) VALUES (:uid, CURRENT_TIMESTAMP, :dept, :notes, :uid2)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
    $stmt->bindValue(':uid2', $addedBy, PDO::PARAM_INT);
    $stmt->bindValue(':dept', $dept, PDO::PARAM_INT);
    $stmt->execute();
}

function checkOut($uid, $autoTime)
{
    global $db;

    $time = date("Y-m-d H:i:s");
    if ($autoTime) $time = $autoTime->format('Y-m-d H:i:s');

    $stmt = $db->prepare("UPDATE `tracker` SET `checkout` = :time, `auto` = :auto WHERE `uid` = :uid AND `checkout` IS NULL");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':time', $time, PDO::PARAM_STR);
    $stmt->bindValue(':auto', isset($autoTime), PDO::PARAM_INT);
    $stmt->execute();
}

function getCheckIn($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT `id`, `dept`, `checkin` FROM `tracker` WHERE `uid` = :uid AND `checkout` IS NULL");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDevmode()
{
    global $db;
    $stmt = $db->prepare("SELECT `devmode` FROM `settings`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['devmode'];
}

function getSiteStatus()
{
    global $db;
    $stmt = $db->prepare("SELECT `site_status` FROM `settings`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['site_status'];
}

function setSiteStatus($status)
{
    global $db;

    $stmt = $db->prepare("UPDATE `settings` SET `site_status` = :status");
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    return true;
}

function setDevmode($status)
{
    global $db;

    $stmt = $db->prepare("UPDATE `settings` SET `devmode` = :status");
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    return true;
}

function findUser($input)
{
    global $db;

    $stmt = $db->prepare("SELECT * FROM `users` WHERE `id` = :id OR `nickname` LIKE :nickname OR CONCAT( first_name,  ' ', last_name ) LIKE  :inputname LIMIT 20");
    $stmt->bindValue(':id', $input, PDO::PARAM_STR);
    $stmt->bindValue(':nickname', '%' . $input . '%', PDO::PARAM_STR);
    $stmt->bindValue(':inputname', '%' . $input . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnclockedUsers()
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `tracker` WHERE `auto` = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addLog($uid, $action, $data)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `logs` (`uid`, `action`, `data`) VALUES (:uid, :action, :data)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':action', $action, PDO::PARAM_STR);
    $stmt->bindValue(':data', $data, PDO::PARAM_STR);
    $stmt->execute();
}

function getClockTime($uid)
{
    $in = getCheckIn($uid);
    if (count($in) == 0) return -1;
    return time() - strtotime($in[0]['checkin']);
}

function getMinutesToday($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT id,checkin,checkout FROM `tracker` WHERE `uid` = :uid AND (DATE(`checkin`) = CURDATE() OR DATE(`checkout`) = CURDATE() OR `checkout` IS NULL)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $time = 0;
    foreach ($periods as $period) {
        $checkout = $period['checkout'];
        if ($checkout == null) {
            $checkout = date("Y-m-d h:i:sa", time());
            //echo "Null: " . $period['id'] . ":" . $checkout;
        }
        $overlap = overlapInMinutes(date("Y-m-d 00:00:01"), date("Y-m-d 23:59:59"), $period['checkin'], $checkout);
        //echo $period['id'] . ":" . $overlap . "\n";
        $time = $time + $overlap;
    }

    return $time;
}

function getMinutesTotal($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT id,checkin,checkout FROM `tracker` WHERE `uid` = :uid");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $time = 0;
    foreach ($periods as $period) {
        $checkout = $period['checkout'];
        if ($checkout == null) $checkout = date("Y-m-d h:i:sa", time());
        $start_date = new DateTime($period['checkin']);
        $since_start = $start_date->diff(new DateTime($checkout));
        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;

        $time = $time + $minutes;
    }

    return $time;
}

// Somewhat inefficient O(N2) queries to get all bonus periods and find all time entries that reside within them.
function calculateBonusTime($uid, $array)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `time_mod`");
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bonus = 0;
    $entries = [];
    $debug = [];

    foreach ($periods as $period) {
        $stmt = $db->prepare("SELECT * FROM `tracker` WHERE `uid` = :uid ORDER BY `checkin` ASC");
        //$stmt = $db->prepare("SELECT * FROM `tracker` WHERE `dept` = :dept AND `uid` = :uid AND (`checkin` BETWEEN :start1 AND :stop1 OR `checkout` BETWEEN :start2 AND :stop2)");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        //$stmt->bindValue(':dept', $period['dept'], PDO::PARAM_INT);
        //$stmt->bindValue(':start1', $period['start'], PDO::PARAM_STR);
        //$stmt->bindValue(':start2', $period['start'], PDO::PARAM_STR);
        //$stmt->bindValue(':stop1', $period['stop'], PDO::PARAM_STR);
        //$stmt->bindValue(':stop2', $period['stop'], PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            // Department check
            $departments = explode(",", $period['dept']);
            if (!$array && !in_array($result['dept'], $departments)) continue;

            if ($result['checkout'] == null) {
                $checkout = date("Y-m-d h:i:sA", time());
            } else {
                $checkout = date("Y-m-d h:i:sA", strtotime($result['checkout']));
                $result['checkout'] = date("M d h:i:sA", strtotime($result['checkout']));
            }

            if (!isset($result['overlap'])) $result['overlap'] = 0;
            if (!isset($result['bonus'])) $result['bonus'] = 0;

            if (in_array($result['dept'], $departments)) {
                $overlap = overlapInMinutes($period['start'], $period['stop'], $result['checkin'], $checkout);
                $result['overlap'] += $overlap;
                $result['bonus'] += ($period['modifier'] * $overlap) - $overlap;
                //echo "<br>OVERLAP: " . $overlap;
            } else {
                //echo "<br>SKIP";
            }

            $debug[$result['id']] = $result;
            $bonus = $bonus + $result['bonus'];
            //$trackers[$period['dept']][] = $result;

            if ($array) {
                $timestamp = strtotime($result['checkin']);

                $worked = strtotime($checkout) - strtotime($result['checkin']);

                // Redo dates for short format
                $checkin = date("M d h:i:sA", strtotime($result['checkin']));

                if (!isset($entries[$timestamp]['bonus'])) $entries[$timestamp]['bonus'] = 0;
                if (!isset($entries[$timestamp]['overlap'])) $entries[$timestamp]['overlap'] = 0;

                $entries[$timestamp]['id'] = $result['id'];
                $entries[$timestamp]['dept'] = $result['dept'];
                $entries[$timestamp]['auto'] = $result['auto'];
                $entries[$timestamp]['worked'] = $worked;
                $entries[$timestamp]['bonus'] += $result['bonus'];
                $entries[$timestamp]['overlap'] += $result['overlap'];
                $entries[$timestamp]['checkin'] = $checkin;
                $entries[$timestamp]['checkout'] = $result['checkout'];
                $entries[$timestamp]['notes'] = $result['notes'];
                $entries[$timestamp]['ongoing'] = !$result['checkout'];
            }
        }
    }

    //echo json_encode($debug);

    return $array ? $entries : $bonus;
}

function overlapInMinutes($startDate1, $endDate1, $startDate2, $endDate2)
{
    $lastStart = $startDate1 >= $startDate2 ? $startDate1 : $startDate2;
    $lastStart = strtotime($lastStart);

    $firstEnd = $endDate1 <= $endDate2 ? $endDate1 : $endDate2;
    $firstEnd = strtotime($firstEnd);

    $overlap = floor(($firstEnd - $lastStart) / 60);

    return $overlap > 0 ? $overlap : 0;
}

function getApps()
{
    return jwt_request("https://reg.goblfc.org/api/volunteers?client_id=4", $_SESSION['accessToken'], array());
}

function jwt_request($url, $token, $post)
{
    header('Content-Type: application/json'); // Specify the type of data
    $ch = curl_init($url); // Initialise cURL
    $post = json_encode($post); // Encode the data array into a JSON string
    $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
    echo $authorization;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization)); // Inject the token into the header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    $result = curl_exec($ch); // Execute the cURL statement
    curl_close($ch); // Close the cURL connection
    return json_decode($result); // Return the received data
}

// Jank-ass permission check until we can do it via API somehow
function isAdmin($id)
{
    $user = getUserByID($id, true);
    if (isset($user[0]) && $user[0]['admin'] == 1) return true;
    return false;
}

function isManager($id)
{
    $user = getUserByID($id, true);
    if (isset($user[0]) && $user[0]['manager'] == 1) return true;
    return false;
}

function isBanned($id)
{
    $user = getUserByID($id, true);
    if (isset($user[0]) && $user[0]['banned'] == 1) return true;
    return false;
}

function escapeText($text)
{
    $esc = htmlspecialchars($text, ENT_QUOTES);
    $esc = str_replace("  ", " &nbsp;", $esc);
    $esc = preg_replace('/^\x20/m', "&nbsp;", $esc);
    $esc = preg_replace('/\x20(?=\r|\n)/m', "&nbsp;", $esc);

    return $esc;
}