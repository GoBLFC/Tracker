<?php

require "main.php";

if (!$isAdmin) die('Unauthorized.');

if (!isset($_GET["page"])) {
    echo $twig->render("admin/site.html");
} else {
    $page = $_GET["page"];
    switch ($page) {
        case "users":
            echo $twig->render("admin/users.html");
            break;
        case "departments":
            echo $twig->render("admin/departments.html");
            break;
        case "bonuses":
            echo $twig->render("admin/bonuses.html");
            break;
        case "rewards":
            echo $twig->render("admin/rewards.html");
            break;
        case "reports":
            echo $twig->render("admin/reports.html");
            break;
        default:
            http_response_code(404);
    }
}

?>
