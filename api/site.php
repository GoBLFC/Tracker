<?php

require "../api.php";

class Site extends API {

    public function setSiteStatus($params) {
        return $this->db->setSiteStatus($params["status"]);
    }

    public function setDevMode($params) {
        return $this->db->setDevMode($params["status"]);
    }

}

$api = new Site($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
