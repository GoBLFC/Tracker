<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/21/2018
 * Time: 4:44 PM
 */

// Update user session
setcookie("badge", $badgeID);
setcookie("session", session_id());
updateSession($badgeID, session_id());
?>

<div class='logo'>
    <img src="assets/img/BLFC-ChipIcon.png"/>
    <?php
    if (isset($action) && $action == "new") {
        echo "<h2>Welcome $badgeID, glad you're on board! Here you'll be able to check in and out.</h2>";
    } else {
        echo "<h2>Good login. Show landing page. $badgeID</h2>";
    }
    ?>
</div>