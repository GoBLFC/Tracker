<?php

require "main.php";

if ($user != null) {
    if ((isset($_SESSION["quickclock"]) && $_SESSION["quickclock"] >= 20) || $isBanned || $siteStatus !== 1 || ($devMode == 0 && $kioskAuth == 0)) {
        if ((isset($_SESSION["quickclock"]) && $_SESSION["quickclock"] >= 20) || $siteStatus == 12) {
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

        if (!$isAdmin && !$isManager) {
            session_unset();
            session_regenerate_id();
        }

        echo $twig->render("disabled.html", [
            "statusClass" => $statusClass,
            "message" => $message,
            "description" => $description
        ]);
    } else {
        if (sizeof($notifs) > 0) {
            echo $twig->render("alert.html");
        } else {
            $cDept = $db->getCheckIn($badgeID)->fetch();
            if ($cDept) $cDept = $cDept[0];
            echo $twig->render("landing.html", [
                "departments" => $db->listDepartments(),
                "tgBot" => urlencode("https://t.me/{$BOT_USERNAME}?start=" . $user["tg_setup_code"]),
                "cDept" => $cDept
            ]);
        }
    }
} else {
    echo $twig->render("login.html");
}
