<?php
if (!defined('TRACKER')) die('No.');

include('sql.php');
include('functions.php');

$session = "";

session_start();
$badgeID = "";
if (isset($_SESSION['badgeid'])) $badgeID = $_SESSION['badgeid'];

if (!isset($_COOKIE["session"])) {
    setcookie("session", session_id(), 0, "/");
    $session = session_id();
} else {
    $session = $_COOKIE["session"];
}


if (isset($_COOKIE["kiosk"])) {
    $kiosksession = $_COOKIE["kiosk"];
} else {
    $kiosksession = "UNAUTHORIZED";
}

if (isset($_GET['logout'])) {
    logout($session);

    session_unset();
    session_regenerate_id();

    setcookie("session", '', 0, "/");
    setcookie("badge", '', 0, "/");
    header('Location: /tracker/');
}