<?php
define('TRACKER', TRUE);

include('includes/header.php');
include('pages/headerhtml.php');

// Check session
$user = isValidSession($session, $badgeID);
$page = "landing";
$siteStatus = getSiteStatus();
$kioskAuth = (isset($_COOKIE['kiosknonce']) && sizeof(checkKiosk($_COOKIE['kiosknonce']))) >= 1 ? 1 : 0;

if (isset($_GET['page'])) $page = $_GET['page'];

if ($page == "admin" && isAdmin($badgeID)) {
    include('pages/admin.php');
} else if ($page == "manage" && isManager($badgeID)) {
    include('pages/manage.php');
} else if ($page == "sso") {
    require_once('vendor/autoload.php');
    include('pages/sso.php');
} else if ($user != null) {
    if ($siteStatus !== 1 || $kioskAuth == 0) {
        include('pages/disabled.php');
    } else {
        include('pages/landing.php');
    }
} else {
    include('pages/login.php');
}

include('includes/footer.php');