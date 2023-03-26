<?php

require "../api.php";

$access = [
    "searchUsers" => Role::Manager,
    "listUsers" => Role::Admin,
    "setRole" => Role::Admin,
    "banUser" => Role::Admin,
    "unbanUser" => Role::Admin
];

class User extends API {

    public function searchUsers($input) {
        /*

        Search for a list of users that match the search query `$input`.
        The query is checked against each user's ID, username, and legal name.

        Parameters:
            - `input` (str): Search query

        Returns:
            An array of zero or more associative arrays, representing users. User row format:
            array(
                "id" => (int),
                "username" => (str),
                "first_name" => (str),
                "last_name" => (str),
                "badge_name" => (str || null),
                "role" => (int)
            )

        */

        return $this->db->searchUsers($input)->fetchAll();
    }

    public function listUsers($role = null) {
        /*

        Lists all users, optionally those with the given `$role`.

        Parameters:
            - `role` (int) (optional): List only users with this role

        Returns:
            An array of zero or more associative arrays, representing users. User row format:
            array(
                "id" => (int),
                "username" => (str),
                "first_name" => (str),
                "last_name" => (str),
                "badge_name" => (str || null),
                "role" => (int)
            )

        */

        $results = $this->db->listUsers(role: $role);
        return $results->fetchAll();
    }

    public function setRole($id, $role) {
        /*

        Sets the role (access level) of a user.

        Parameters:
            - `id` (int): ID of the user
            - `role` (int): Role to apply

        Returns:
            array("success" => (bool), "message" => (str))

        */

        if ($_SESSION["badgeid"] == $id) {
            return $this->error("You can't modify your own role");
        }

        $result = $this->db->setUserRole($id, $role);

        if (!$result->rowCount()) {
            return $this->error("User with ID $id not found");
        }

        return $this->success("Role updated");
    }

    public function banUser($id) {
        /*

        Ban a user. This also revokes whatever role they have.

        Parameters:
            - `id` (int): ID of the user

        Returns:
            array("success" => (bool), "message" => (str))

        */

        if ($_SESSION["badgeid"] == $id) {
            return $this->error("You can't ban yourself");
        }

        $result = $this->db->setUserBan($id, true);

        if (!$result->rowCount()) {
            return $this->error("User with ID $id not found");
        }

        return $this->success("User banned");
    }

    public function unbanUser($id) {
        /*

        Unban a user. This reinstates them with a Volunteer role (no admin permissions).

        Parameters:
            - `id` (int): ID of the user

        Returns:
            array("success" => (bool), "message" => (str))

        */

        if ($_SESSION["badgeid"] == $id) {
            return $this->error("You can't unban yourself");
        }

        $result = $this->db->setUserBan($id, false);

        if (!$result->rowCount()) {
            return $this->error("User with ID $id not found");
        }

        return $this->success("User unbanned");
    }

}

$api = new User($db);
echo apiCall($api, $access, $role, $_POST);

?>
