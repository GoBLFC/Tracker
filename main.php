<?php

// Main definitions file to be required at the top of most, if not all, pages.

define("ROOT_DIR", __DIR__);

require ROOT_DIR . "/config.php";

if ($TIMEZONE) {
    date_default_timezone_set($TIMEZONE);
}

include ROOT_DIR . "/database.php";
include ROOT_DIR . "/classes.php";
include ROOT_DIR . "/vendor/autoload.php";

$session = "";

if(!isset($_SESSION)) session_start();
$badgeID = "";
if (isset($_SESSION['badgeid'])) $badgeID = $_SESSION['badgeid'];

if (isset($_COOKIE["kiosk"])) {
    $kiosksession = $_COOKIE["kiosk"];
} else {
    $kiosksession = "UNAUTHORIZED";
}

$loader = new \Twig\Loader\FilesystemLoader(ROOT_DIR . "/templates");
$twig = new \Twig\Environment($loader);

$db = new Database($DB_HOST, $DB_NAME, $DB_USERNAME, $DB_PASSWORD);

// Check valid session
if ($badgeID == "") {
    $user = false;
} else {
    $user = $db->getUser($badgeID)->fetch();
    if ($user == null) {
        $user = false;
    } elseif ($user['id'] != $badgeID) {
        $user = false;
    }
}

$devMode = $db->getDevMode();
$siteStatus = $db->getSiteStatus();
$kioskAuth = (isset($_COOKIE["kiosknonce"]) && $db->checkKiosk($_COOKIE["kiosknonce"])->fetch()) ? 1 : 0;

enum Role: int {
    case Admin = 3;
    case Manager = 2;
    case Lead = 1;
    case Volunteer = 0;
}

$roleDB = $db->getUserRole($badgeID)->fetch();

if ($roleDB) {
    $role = $roleDB["role"];
} else {
    $role = false;
}

$isAdmin = $role ? $role >= Role::Admin->value : false;
$isManager = $role ? $role >= Role::Manager->value : false;
$isLead = $role ? $role >= Role::Lead->value : false;

$isBanned = $db->getUserBan($badgeID);

$twig->addGlobal("user", $user);
$twig->addGlobal("devMode", $devMode);
$twig->addGlobal("siteStatus", $siteStatus);
$twig->addGlobal("kioskAuth", $kioskAuth);
$twig->addGlobal("isAdmin", $isAdmin);
$twig->addGlobal("isManager", $isManager);
$twig->addGlobal("isLead", $isLead);
$twig->addGlobal("isBanned", $isBanned);

?>
