<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/21/2018
 * Time: 4:29 PM
 */

function isValidSession($session, $badge)
{
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

function createUser($id, $lName, $session)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO `users` (`id`, `last_name`, `usergroups`, `last_session`, `staff_salt`, `staff_pass`, `registered`, `reg_ua`) VALUES (:id, :lName, 1, :lastsession, '', '', NOW(), :regua)");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':lName', $lName, PDO::PARAM_STR);
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->bindValue(':regua', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
    $stmt->execute();
    //return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateSession($id, $session)
{
    global $db;
    $stmt = $db->prepare("UPDATE `users` SET `last_session` = :lastsession, `last_ip` = :lastip WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->bindValue(':lastip', $_SERVER["HTTP_CF_CONNECTING_IP"], PDO::PARAM_STR);
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

function isCheckedIn($uid)
{
    global $db;
    $stmt = $db->prepare("SELECT `id`, `dept` FROM `tracker` WHERE `uid` = :uid AND `checkout` IS NULL");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}