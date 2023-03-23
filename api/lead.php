<?php

require "../api.php";

if (!($isLead || $isManager || $isAdmin)) {
    http_response_code(403);
    echo error("Unauthorized");
    exit();
}

class Lead extends API {

    public function setKioskAuth($params) {
        $status = $params["status"];

        if ($status == 1) {
            $kioskNonce = bin2hex(random_bytes(16));
            $this->db->authorizeKiosk($kioskNonce);
            echo $this->success($kioskNonce);
        }

        if ($status == 0) {
            $this->db->deauthorizeKiosk($_COOKIE["kiosknonce"]);
            echo $this->success(1);
        }
    }

}

$api = new Lead($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
