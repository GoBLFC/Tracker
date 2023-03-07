<?php
require "../config.php";
session_start();
header("Refresh:0; url={$OAUTH_CONCAT_BASE_URL}/oauth/logout?next={urlencode($CANONICAL_URL}&client_id={$OAUTH_CLIENT_ID}&access_token=" . $_SESSION['accessToken'], true, 303);