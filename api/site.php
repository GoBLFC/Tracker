<?php

require "../api.php";

$access = [
    "enableSite" => Role::Admin,
    "disableSite" => Role::Admin,
    "enableDevMode" => Role::Admin,
    "disableDevMode" => Role::Admin
];

class Site extends API {

    public function enableSite() {
        /*

        Enables the site.

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->setSiteStatus(true);
        return $this->success("Site enabled");
    }

    public function disableSite() {
        /*

        Disables the site.

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->setSiteStatus(false);
        return $this->success("Site disabled");
    }

    public function enableDevMode() {
        /*

        Enables Dev Mode.

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->setDevMode(true);
        return $this->success("Dev Mode enabled");
    }

    public function disableDevMode() {
        /*

        Disables Dev Mode.

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->setDevMode(false);
        return $this->success("Dev Mode disabled");
    }

}

$api = new Site($db);
echo apiCall($api, $access, $role, $_POST);

?>
