<?php

require "../api.php";

$access = [
    "createReward" => Role::Admin,
    "updateReward" => Role::Admin,
    "listRewards" => Role::Manager,
    "claimReward" => Role::Manager,
    "unclaimReward" => Role::Manager,
    "listClaims" => Role::Manager
];

class Reward extends API {

    // Rewards

    public function createReward($name, $description, $hours) {
        /*

        Create a new reward.

        Parameters:
            - `name` (str): Name of the reward
            - `description` (str): Description of the reward
            - `hours` (bool): Number of hours required before the reward can be claimed

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->createReward($name, $description, $hours);
        return $this->success("Reward created");
    }

    public function updateReward($id, $name, $desc, $hours) {
        /*

        Update an existing reward.

        Parameters:
            - `id` (int): ID of the reward
            - `name` (str): New name of the reward
            - `desc` (str): New description of the reward
            - `hours` (int): Number of hours required before the reward can be claimed

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->updateReward($id, $name, $desc, $hours);
        return $this->success("Reward updated");
    }

    public function listRewards() {
        /*

        List all rewards.

        Returns:
            An array of zero or more associative arrays, representing rewards. Reward row format:
            array(
                "id" => (int),
                "name" => (str),
                "desc" => (str),
                "hours" => (int)
            )

        */

        return $this->db->listRewards()->fetchAll();
    }

    // Claims

    public function claimReward($uid, $claim) {
        /*

        Claim a reward.

        Parameters:
            - `uid` (int): ID of the user claiming the reward
            - `claim` (int): ID of the reward to be claimed

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->claimReward($uid, $claim);
        return $this->success("Reward claimed");
    }

    public function unclaimReward($uid, $claim) {
        /*

        Unclaim a reward (undo a claim).

        Parameters:
            - `uid` (int): ID of the user to reverse the claim for
            - `claim` (int): ID of the reward to be unclaimed

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->unclaimReward($uid, $claim);
        return $this->success("Reward unclaimed");
    }

    public function listClaims($id) {
        /*

        List all claimed rewards for a particular user.

        Parameters:
            - `id` (int): ID of the user to list claims for

        Returns:
            An array of zero or more ints, representing IDs of rewards that have been claimed:
            array((int), [...])

        */

        // Turn a list of associative arrays into a list of ints, since there's only one column to work with.
        $claims = [];
        $results = $this->db->listRewardClaims($id)->fetchAll();
        foreach ($results as $claim) {
            $claims[] = $claim["claim"];
        }

        return $claims;
    }

}

$api = new Reward($db);
echo apiCall($api, $access, $role, $_POST);

?>
