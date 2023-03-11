<?php

require "main.php";

if (!$isLead) die('Unauthorized.');

echo $twig->render("lead.html");

?>
