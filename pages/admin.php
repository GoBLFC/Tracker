<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/9/2019
 * Time: 10:56 PM
 */

if (!defined('TRACKER')) die('No.');
if (!isAdmin($badgeID)) die('Unauthorized.');

$status = getSiteStatus();
$kioskAuth = sizeof(checkKiosk(session_id())) >= 1 ? 1 : 0;
?>

<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header highvis">
            Admin
        </div>
        <div class="row">
            <div class="col-sm" id="currdurr">
                <div class="card-body">
                    <button data-status=<?php echo $status ?> onclick="toggleSetting(this,
                    'Disable', 'Enable', 'Disabling', 'Enabling', 'Site', 'setSiteStatus', 'btn-success btn-danger')"
                    type="button" class="btn btn-sm btn-<?php echo($status == 1 ? "danger" : "success") ?>
                    "><?php echo($status == 1 ? "Disable" : "Enable") ?> Site
                    </button>
                    <button data-status=<?php echo $kioskAuth ?> onclick="toggleSetting(this,
                    'De-Authorize', 'Authorize', 'De-Authorizing', 'Authorizing', 'Kiosk', 'setKioskAuth',
                    'btn-warning btn-danger')"
                    type="button" class="btn btn-sm btn-<?php echo($kioskAuth == 1 ? "danger" : "warning") ?>
                    "><?php echo($kioskAuth == 1 ? "De-Authorize" : "Authorize") ?> Kiosk
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
<script src="js/admin.js"></script>
<script>$(document).ready(function () {
        decrementLogout();
    });
</script>