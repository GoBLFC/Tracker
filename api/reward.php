<?php

require "../api.php";

class Reward extends API {

    public function getRewards($params) {
        return $this->db->listRewards()->fetchAll();
    }

    public function addReward($params) {
        return $this->db->createReward($params["name"], $params["description"], $params["hours"], $params["hidden"]);
    }

    public function updateReward($params) {
        return $this->db->updateReward($params["id"], $params["field"], $params["value"]);
    }

    public function getRewardClaims($params) {
        return $this->db->listRewardClaims($params["id"])->fetchAll();
    }

    public function claimReward($params) {
        return $this->db->claimReward($params["uid"], $params["type"]);
    }

    public function unclaimReward($params) {
        return $this->db->unclaimReward($params["uid"], $params["type"]);
    }

}

$api = new Reward($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
