<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 1/29/2019
 * Time: 11:27 PM
 */
if (!defined('TRACKER')) die('No.');

header( "Refresh:2; url=/tracker?sso=true", true, 303);
?>

<div class="card" style="width: 25rem;top: 8em;">
    <div class="card-body">
        <p class="card-text"><h2>BLFC Volunteer Check-In</h2></p>
        <p class="card-text"><div class="alert alert-dark" role="alert">Redirecting to SSO...</div></p>
    </div>
</div>