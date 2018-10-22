<?php
include('sql.php');
include('functions.php');

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
?>

<html>
<head>
    <link rel="stylesheet" media="all" href="css/style.css">
    <link rel="stylesheet" media="all" href="css/landing.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
