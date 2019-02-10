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

function logout($session)
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

function getUserByID($id)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `users` WHERE id = :id");
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

function getDepartments($hidden)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `departments` WHERE `hidden` = $hidden");
    $stmt->execute();

    $departments = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $dept) $departments[$dept['id']] = $dept;

    return $departments;
}

//Insert or update - excessive binds are because you can't re-use values, silly.
function updateSession($id, $fName, $lName, $nName, $session)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `users` (`id`, `first_name`, `last_name`, `nickname`, `usergroups`, `last_session`, `last_ip`, `staff_salt`, `staff_pass`, `registered`, `reg_ua`) VALUES (:id, :fName, :lName, :nName, 1, :lastsession, :lastip, '', '', NOW(), :regua) ON DUPLICATE KEY UPDATE `first_name` = :fName2, `last_name` = :lName2, `nickname` = :nName2, `last_session` = :lastsession2, `last_ip` = :lastip2");
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
    setcookie("kiosk", session_id());

    global $db;
    $stmt = $db->prepare("INSERT INTO `kiosks` (`session`, `authorized`) VALUES (:sess, CURRENT_TIMESTAMP)");
    $stmt->bindValue(':sess', $session, PDO::PARAM_STR);
    $stmt->execute();
}

function checkIn($uid, $dept)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `tracker` (`uid`, `checkin`, `dept`, `notes`, `addedby`) VALUES (:uid, CURRENT_TIMESTAMP, :dept, '', :uid2)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':uid2', $uid, PDO::PARAM_INT);
    $stmt->bindValue(':dept', $dept, PDO::PARAM_INT);
    $stmt->execute();
}

function checkOut($uid)
{
    global $db;

    $stmt = $db->prepare("UPDATE `tracker` SET `checkout` = CURRENT_TIMESTAMP WHERE `uid` = :uid AND `checkout` IS NULL");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
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

function getClockTime($uid)
{
    $in = getCheckIn($uid);
    if (count($in) == 0) return -1;
    return time() - strtotime($in[0]['checkin']);
}

function getMinutesToday($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT id,checkin,checkout FROM `tracker` WHERE `uid` = :uid AND (DATE(`checkin`) = CURDATE() OR DATE(`checkout`) = CURDATE())");
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
function calculateBonusTime($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM `time_mod`");
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bonus = 0;
    $trackers = [];
    $debug = [];

    foreach ($periods as $period) {
        $stmt = $db->prepare("SELECT * FROM `tracker` WHERE `uid` = :uid ");
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
            if (!in_array($result['dept'], $departments)) continue;

            $overlap = overlapInMinutes($period['start'], $period['stop'], $result['checkin'], $result['checkout']);
            $result['overlap'] = $overlap;
            $result['bonus'] = ($period['modifier'] * $overlap) - $overlap;
            $debug[$result['id']] = $result;
            $bonus = $bonus + $result['bonus'];
            $trackers[$period['dept']][] = $result;
        }
    }

    //echo json_encode($debug);

    return $bonus;
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

// Jank-ass permission check until we can do it via API somehow
function isAdmin($id)
{
    $ids = array(5867, 13685);
    return in_array($id, $ids);
}

function isManager($id)
{
    $ids = array(5867, 13685);
    return in_array($id, $ids);
}