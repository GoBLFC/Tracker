<?php

require "../api.php";

$access = [
    "createBonus" => Role::Admin,
    "deleteBonus" => Role::Admin,
    "listBonuses" => Role::Admin
];

class Bonus extends API {

    public function createBonus($start, $stop, $depts, $modifier) {
        /*

        Create a new bonus time period.

        Parameters:
            - `start` (str): Bonus start time in SQL format
            - `stop` (str): Bonus stop time in SQL format
            - `depts` (str): Comma-delimited list of department IDs the bonus applies to
            - `modifier` (float): Time multiplier

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->createBonus($start, $stop, $depts, $modifier);
        return $this->success("Bonus created");
    }

    public function deleteBonus($id) {
        /*

        Delete a bonus time period with the given `$id`.

        Parameters:
            - `id` (int): ID of bonus time

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->deleteBonus($id);
        return $this->success("Bonus deleted");
    }

    public function listBonuses() {
        /*

        List all bonuses.

        Returns:
            An array of zero or more associative arrays, representing bonuses. Bonus row format:
            array(
                "id" => (int),
                "start" => (str),
                "stop" => (str),
                "dept" => (str),
                "modifier" => (float)
            )

        */

        return $this->db->listBonuses()->fetchAll();
    }

}

$api = new Bonus($db);
echo apiCall($api, $access, $role, $_POST);

?>
