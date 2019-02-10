<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/10/2019
 * Time: 3:57 AM
 */

if ($siteStatus == 0) {
    $statusClass = "siteDisabled";
    $message = "SITE DISABLED!";
} else if ($kioskStatus == 0) {
    $statusClass = "noKiosk";
    $message = "Device not authorized.";
} else if ($siteStatus == 12) {
    $statusClass = "onFire";
    $message = "SITE ON FIRE!!";
}

?>


<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header highvis <?php echo $statusClass ?>">
            <?php echo $message ?>
        </div>
    </div>

    <?php
    include('pages/adminFunctions.php');
    ?>
</div>