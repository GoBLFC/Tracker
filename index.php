<?php
include('includes/header.php');

// Check session
$user = isValidSession($session, $badgeID);
if ($user != null) {
    //echo "Valid session.";
    include('pages/landing.php');
} else {
    //echo "Invalid or no session.";
    include('pages/login.php');
}

include('includes/footer.php');