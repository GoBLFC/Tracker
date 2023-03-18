<?php

require "../api.php";

if (!($isLead || $isManager || $isAdmin)) {
    http_response_code(403);
    echo json_encode(["code" => 0, "msg" => "Unauthorized"]);
    exit();
}

switch ($action) {
    case "setKioskAuth":
        $status = $_POST["status"];

        if ($status == 1) {
            $kioskNonce = bin2hex(random_bytes(16));
            $db->authorizeKiosk($kioskNonce);
            echo json_encode(["code" => 1, "val" => $kioskNonce]);
        }

        if ($status == 0) {
            $db->deauthorizeKiosk($_COOKIE["kiosknonce"]);
            echo json_encode(["code" => 1, "val" => 1]);
        }
}

?>
