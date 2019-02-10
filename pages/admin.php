<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/9/2019
 * Time: 10:56 PM
 */

if (!defined('TRACKER')) die('No.');
if (!isAdmin($badgeID)) die('Unauthorized.');
?>

<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header highvis">
            Admin
        </div>
        <div class="row">
            <div class="col-sm" id="currdurr">
                <div class="card-body">
                    <button data-state="enabled" onclick="toggleSite(this)"
                            type="button" class="btn btn-sm btn-danger">Disable Site
                    </button>
                    <button data-state="enabled" onclick="authKiosk(this)"
                            type="button" class="btn btn-sm btn-warning">De-Authorize Kiosk
                    </button>
                    <a href="/tracker/" style="float:right"
                       role="button" class="btn btn-sm btn-info">BACK
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="top: 5em;position: relative;">
    <div class="card novis">
        <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...</div>
    </div>
</div>

<script src="js/landing.js"></script>
<script>$(document).ready(function () {
        decrementLogout();
    });
</script>