<?php
if (!defined('TRACKER')) die('No.');

require ROOT_DIR . "/config.php";

include('sql.php');
include('functions.php');

$session = "";

if(!isset($_SESSION)) session_start();
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
    logoutSession($session);

    setcookie("session", '', 0, "/");
    setcookie("badge", '', 0, "/");
    //header('Location: /');
	
	if (isset($_SESSION['accessToken'])){
		header("Refresh:0; url={$OAUTH_CONCAT_BASE_URL}/oauth/logout?next={urlencode($CANONICAL_URL)}&client_id={$OAUTH_CLIENT_ID}&access_token=" . $_SESSION['accessToken'], true, 303);
	}else{
		header("Refresh:0; url=$CANONICAL_URL", true, 303);
	}
	
	session_unset();
    session_regenerate_id();
	
    die();
}