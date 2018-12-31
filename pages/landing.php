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

// Load department list
$departments = getDepartments(0);
?>

<div class='logo'>
    <img src="assets/img/BLFC-ChipIcon.png"/>
    <?php
    if (isset($action) && $action == "new") {
        echo "<h2>Welcome $badgeID, glad you're on board! Here you'll be able to check in and out.</h2>";
    } else {
        echo "<h2>Welcome $badgeID!</h2>";
    }
    ?>
</div>

<div class="box">
    <div class="box__header">
        <h3 class="box__header-title">Volunteer Panel</h3>
    </div>
    <div class="box__body">
        <div class="stats stats--main">
            <div class="stats__amount">0</div>
            <div class="stats__caption">hours</div>
            <div class="stats__change">
                <div class="stats__value stats__value--positive">0</div>
                <div class="stats__period">today</div>
            </div>
        </div>

        <!--
        <div class="stats">
            <div class="stats__amount">87359</div>
            <div class="stats__caption">visitors</div>
            <div class="stats__change">
                <div class="stats__value stats__value--negative">-12%</div>
                <div class="stats__period">this week</div>
            </div>
        </div>

        <div class="stats">
            <div class="stats__amount">6302</div>
            <div class="stats__caption">comments</div>
            <div class="stats__change">
                <div class="stats__value">+78</div>
                <div class="stats__period">this week</div>
            </div>
        </div>

        <div class="stats">
            <div class="stats__amount">268</div>
            <div class="stats__caption">posts</div>
            <div class="stats__change">
                <div class="stats__value">+3</div>
                <div class="stats__period">this week</div>
            </div>
        </div>
        -->
    </div>

    <span class="custom-dropdown big">
        <select>
            <option disabled selected hidden>Select Department</option>
            <?php foreach ($departments as $dept) echo "<option value='" . $dept['id'] . "'>" . $dept['name'] . "</option>"; ?>
        </select>
    </span>
    <a href="#" class="button"><span>Check-In</span></a>

</div>

<div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...</div>

<script>
    $('.button').mouseup(function () {
        $(this).toggleClass("button-loading");
    });

    let logoutTime = 60;

    $(document).ready(function () {
        decrementLogout();
    })

    function decrementLogout() {
        if (logoutTime === 1) $('#gram').text("second");
        if (logoutTime === 0) $('.autologout').html("Goodbye!");
        if (logoutTime === -1) {
            window.location.href = "/tracker/?logout=timeout";
            return;
        }

        setTimeout(function () {
            logoutTime--;
            if (logoutTime > 0) $('#lsec').text(logoutTime);
            decrementLogout();
        }, 1000);
    }
</script>