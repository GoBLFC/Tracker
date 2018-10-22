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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $stmt = $db->prepare("UPDATE `users` SET `last_session` = :lastsession WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->execute();
}