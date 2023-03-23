<?php

require "main.php";

if ($user != null) {
    if ($isBanned || $siteStatus !== 1 || ($devMode == 0 && $kioskAuth == 0)) {
        if ($isBanned) {
            $message = "Not Permitted";
            $description = "There is a hold on your volunteer account. Please talk to a volunteer manager at the volunteer desk.";
        } else if (!$siteStatus) {
            $message = "Site Disabled";
            $description = "Site is disabled for maintenance.";
        } else if (!$kioskAuth) {
            $message = "Device Not Authorized";
            $description = "If you believe this is in error, please contact a volunteer manager.";
        }

        echo $twig->render("disabled.html", [
            "message" => $message,
            "description" => $description
        ]);
    } else {
        $notifs = $db->listNotifications($badgeID)->fetchAll();
        if (sizeof($notifs) > 0) {
            echo $twig->render("alert.html", [
                "notifs" => $notifs
            ]);
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
