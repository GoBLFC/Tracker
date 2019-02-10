<?php
define('TRACKER', TRUE);

include('includes/header.php');
include('pages/headerhtml.php');

// Check session
$user = isValidSession($session, $badgeID);
$page = "landing";
if (isset($_GET['page'])) $page = $_GET['page'];

if ($page == "admin" && isAdmin($badgeID)) {
    include('pages/admin.php');
} else if ($page == "manage" && isManager($badgeID)) {
    include('pages/manage.php');
} else if ($page == "sso") {
    require_once('vendor/autoload.php');
    include('pages/sso.php');
} else if ($user != null) {
    $kioskStatus = sizeof(checkKiosk(session_id()));
    $siteStatus = getSiteStatus();

    if ($siteStatus !== 1 || $kioskStatus == 0) {
        include('pages/disabled.php');
    } else {
        include('pages/landing.php');
    }
} else {
    include('pages/login.php');
}

include('includes/footer.php');