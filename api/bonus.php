<?php

require "../api.php";

class Bonus extends API {

    public function getBonuses($params) {
        return $this->db->listBonuses()->fetchAll();
    }

    public function removeBonus($params) {
        return $this->db->deleteBonus($params["id"]);
    }

    public function addBonus($params) {
        return $this->db->createBonus($params["start"], $params["stop"], $params["depts"], $params["modifier"]);
    }

}

$api = new Bonus($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
