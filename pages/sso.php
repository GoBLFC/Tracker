<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 1/29/2019
 * Time: 11:58 PM
 */
if (!defined('TRACKER')) die('No.');

header("Refresh:2; url=/tracker", true, 303);

//SSO Placeholder
$_SESSION['badgeid'] = 1234;
$badgeID = $_SESSION['badgeid'];

session_regenerate_id();

setcookie("badge", $badgeID);
setcookie("session", session_id());
updateSession($badgeID, session_id());
?>

<div class="card" style="width: 25rem;top: 8em;">
    <div class="card-body">
        <p class="card-text">
        <h2>SSO PLACEHOLDER</h2></p>
        <p class="card-text">
        <div class="alert alert-success" role="alert">SSO Success!</div>
        </p>
    </div>
</div>
