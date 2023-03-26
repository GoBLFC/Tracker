<?php

require "../api.php";

$access = [
    "authorizeKiosk" => Role::Lead,
    "deauthorizeKiosk" => Role::Lead
];

class Kiosk extends API {

    public function authorizeKiosk() {
        /*

        Authorize kiosk by setting a kiosk ID.

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $kioskID = bin2hex(random_bytes(16));
        $this->db->authorizeKiosk($kioskID);
        setcookie("KioskID", $kioskID, strtotime("+7 days"));
        return $this->success("Kiosk authorized");
    }

    public function deauthorizeKiosk() {
        /*

        Deauthorize kiosk and unset the kiosk ID.

        Returns:
            array("success" => (bool), "message" => (str))

        */

        if (!array_key_exists("KioskID", $_COOKIE)) {
            return $this->error("Already not authorized");
        }

        $kioskID = $_COOKIE["KioskID"];
        $result = $this->db->deauthorizeKiosk($kioskID);

        if (!$result->rowCount()) {
            return $this->error("No such kiosk ID");
        }

        setcookie("KioskID", $kioskID, 1);

        return $this->success("Kiosk deauthorized");
    }

}

$api = new Kiosk($db);
echo apiCall($api, $access, $role, $_POST);

?>
