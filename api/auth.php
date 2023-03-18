<?php

header("Content-Type: application/json");

require "../main.php";

$action = $_POST["action"];

if ($user == null && isset($action) && $action == "checkQuickCode") {
    $auth = $db->checkQuickCode($_POST["quickcode"])->fetch();
    if (!$auth) {
        echo json_encode(["code" => -1]);
    } else {
        $_SESSION["badgeid"] = $auth["id"];
        echo json_encode(["code" => 1, "id" => $auth["id"]]);
    }
}

?>
