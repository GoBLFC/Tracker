<?php

require "main.php";

if (!($isManager || $isAdmin)) die('Unauthorized.');

echo $twig->render("manage.html", [
    "rewards" => $db->listRewards(hidden: true)->fetchAll()
]);

?>
