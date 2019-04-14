<?php
define('TRACKER', TRUE);

include('includes/header.php');

// TODO: Replace most of the individual variables with user/site objects to reduce queries and make it cleaner

// Check session
$user = isValidSession($session, $badgeID);
$page = "landing";
$devMode = getDevmode();
$siteStatus = getSiteStatus();
$kioskAuth = (isset($_COOKIE['kiosknonce']) && sizeof(checkKiosk($_COOKIE['kiosknonce']))) >= 1 ? 1 : 0;
$isAdmin = isAdmin($badgeID);
$isManager = isManager($badgeID);
$isBanned = isbanned($badgeID);

include('pages/headerhtml.php');

if (isset($_GET['page'])) $page = $_GET['page'];

if ($page == "admin" && $isAdmin) {
    include('pages/admin.php');
} else if ($page == "manage" && ($isManager || $isAdmin)) {
    include('pages/manage.php');
} else
    if ($page == "sso") {
        require_once('vendor/autoload.php');
        include('pages/sso.php');
    } else if ($user != null) {
        if ($_SESSION['quickclock'] >= 20 || $isBanned || $siteStatus !== 1 || ($devMode == 0 && $kioskAuth == 0)) {
            include('pages/disabled.php');
        } else {
            include('pages/landing.php');
        }
    } else {
        include('pages/login.php');
    }

include('includes/footer.php');