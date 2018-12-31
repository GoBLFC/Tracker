<?php
include('sql.php');
include('functions.php');

if (isset($_GET['logout'])) {
    session_destroy();
    setcookie("session", '');
    setcookie("badge", '');
    header('Location: /tracker/');
} else {
    session_start();

    $session = "";
    if (!isset($_COOKIE["session"])) {
        setcookie("session", session_id());
        $session = session_id();
    } else {
        $session = $_COOKIE["session"];
    }

    if (isset($_COOKIE["badge"])) {
        $badgeID = $_COOKIE["badge"];
    }

    if (isset($_COOKIE["kiosk"])) {
        $kiosksession = $_COOKIE["kiosk"];
    } else {
        $kiosksession = "UNAUTHORIZED";
    }
}
?>

<html>
<head>
    <link rel="stylesheet" media="all" href="css/style.css">
    <link rel="stylesheet" media="all" href="css/landing.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
