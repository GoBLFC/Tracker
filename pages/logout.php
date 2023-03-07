<?php
require "../config.php";
session_start();
$encoded_url = urlencode($CANONICAL_URL);
header("Refresh:0; url={$OAUTH_CONCAT_BASE_URL}/oauth/logout?next=$encoded_url&client_id={$OAUTH_CLIENT_ID}&access_token=" . $_SESSION['accessToken'], true, 303);