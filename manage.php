<?php

require "main.php";

if (!($isManager || $isAdmin)) die('Unauthorized.');

echo $twig->render("manage.html", [
    "rewards" => getRewards(false, true)
]);

?>
