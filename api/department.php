<?php

require "../api.php";

class Department extends API {

    public function getDepts($params) {
        $depts = [];
        foreach ($this->db->listDepartments() as $dept) $depts[$dept["id"]] = $dept;
        return $depts;
    }

    public function addDept($params) {
        return $this->db->createDepartment($params["name"], $params["hidden"]);
    }

    public function updateDept($params) {
        return $this->db->updateDepartment($params["id"], $params["name"], $params["hidden"]);
    }

}

$api = new Department($db, $badgeID);
echo apiCall($api, $_POST["func"], $_POST);

?>
