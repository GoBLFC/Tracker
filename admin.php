<?php

require "main.php";

if (!$isAdmin) die('Unauthorized.');

echo $twig->render("admin.html");

?>
