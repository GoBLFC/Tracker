<?php
/**
 * Created by PhpStorm.
 * User: Johnny
 * Date: 4/4/2019
 * Time: 10:23 AM
 */

if (!defined('TRACKER')) die('No.');
?>

<div class="card" style="width: 25rem;top: 8em;">
    <div class="card-body">
        <p class="card-text">
        <h2>ALERT</h2></p>
        <p class="card-text">
            <?php
            foreach ($notifs as $not) {
                echo "<div class=\"alert alert-dark\" role=\"alert\" style=\"text-align: center\">" . $not['message'] . "</div>";
            }
            ?>

            <a role="button" class="btn btn-success btn-sm" onclick="ackAllNotifs(function(data){location.reload()});"
               style="width: 100%">
                I Acknowledge</a>
        </p>
        <div class="card novis">
            <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...
                <a role="button" class="btn btn-light btn-sm" href="/?logout" style="">Logout Now</a>
            </div>
        </div>
    </div>
</div>

<script src="js/landing.js"></script>
<script>$(document).ready(function () {
        decrementLogout();
    });
</script>