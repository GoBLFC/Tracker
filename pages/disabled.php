<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/10/2019
 * Time: 3:57 AM
 */

if ((isset($_SESSION['quickclock']) && $_SESSION['quickclock'] >= 20) || $siteStatus == 12) {
    $statusClass = "onFire";
    $message = "SITE ON FIRE!!";
    $description = "Something has gone terribly wrong. We blame you.";
    $description .= "<audio id='audio' preload=\"auto\" src=\"/assets/egg/meltdown.ogg\" autoplay=\"\"></audio><script>document.getElementById(\"audio\").volume = 0.35; setTimeout(function(){window.location.reload()}, 10000)();</script>";
    $_SESSION['quickclock'] = 0;
} else if ($isBanned) {
    $statusClass = "siteDisabled";
    $message = "Not Permitted.";
    $description = "There is a hold on your volunteer account. Please talk to a volunteer manager at the volunteer desk.";
} else if ($siteStatus == 0) {
    $statusClass = "siteDisabled";
    $message = "Site disabled.";
    $description = "Site is disabled for maintenance.";
} else if ($kioskAuth == 0) {
    $statusClass = "noKiosk";
    $message = "Device not authorized.";
    $description = "If you believe this is in error, please contact a volunteer manager.";
}

if (!isAdmin($badgeID) && !isManager($badgeID)) {
    logoutSession($session);
    session_unset();
    session_regenerate_id();
}
?>

    <div class="container" style="top: 5em;position: relative;">
        <div class="card">
            <div class="card-header highvis <?php echo $statusClass ?>">
                <div class="vistext"><?php echo $message ?></div>
            </div>
            <div class="card-body">
                <?php echo $description ?>
            </div>
        </div>

        <?php
        include('pages/adminFunctions.php');
        ?>
    </div>

<?php
if (isAdmin($badgeID) || isManager($badgeID)) {
    ?>
    <div class="container" style="top: 5em;position: relative;">
        <div class="card novis">
            <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...
                <a role="button" class="btn btn-light btn-sm" href="/?logout" style="">Logout Now</a>
            </div>
        </div>
    </div>
    <script src="js/landing.js"></script>
    <script>$(document).ready(function () {
            decrementLogout();
        });
    </script>
    <?php
}
?>