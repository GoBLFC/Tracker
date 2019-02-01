<?php
define('TRACKER', TRUE);

include('includes/header.php');
include('pages/headerhtml.php');

// Check session
$user = isValidSession($session, $badgeID);

if ($user != null || isset($_GET['dev'])) {
    include('pages/landing.php');
} else if (isset($_GET['sso'])) {
    include('pages/sso.php');
} else {
    include('pages/login.php');
}

include('includes/footer.php');