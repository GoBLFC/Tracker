<?php

// Main definitions file to be required at the top of most, if not all, pages.

define("ROOT_DIR", __DIR__);

require ROOT_DIR . "/config.php";

include ROOT_DIR . "/includes/sql.php";
include ROOT_DIR . "/includes/functions.php";
include ROOT_DIR . "/vendor/autoload.php";

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

$loader = new \Twig\Loader\FilesystemLoader(ROOT_DIR . "/templates");
$twig = new \Twig\Environment($loader);

$user = isValidSession($session, $badgeID);
$page = "landing";
$devMode = getDevmode();
$siteStatus = getSiteStatus();
$kioskAuth = (isset($_COOKIE["kiosknonce"]) && sizeof(checkKiosk($_COOKIE["kiosknonce"]))) >= 1 ? 1 : 0;
$isAdmin = isAdmin($badgeID);
$isManager = isManager($badgeID);
$isLead = isLead($badgeID);
$isBanned = isbanned($badgeID);
$notifs = getNotifications($badgeID, 1);

$twig->addGlobal("user", $user);
$twig->addGlobal("page", $page);
$twig->addGlobal("devMode", $devMode);
$twig->addGlobal("siteStatus", $siteStatus);
$twig->addGlobal("kioskAuth", $kioskAuth);
$twig->addGlobal("isAdmin", $isAdmin);
$twig->addGlobal("isManager", $isManager);
$twig->addGlobal("isLead", $isLead);
$twig->addGlobal("isBanned", $isBanned);
$twig->addGlobal("notifs", $notifs);

?>
