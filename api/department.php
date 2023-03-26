<?php

require "../api.php";

$access = [
    "createDepartment" => Role::Admin,
    "updateDepartment" => Role::Admin,
    "listDepartments" => Role::Manager
];

class Department extends API {

    public function createDepartment($name, $hidden = false) {
        /*

        Create a new department.

        Parameters:
            - `name` (str): Name of the department
            - `hidden` (bool) (optional): Whether this department is hidden from non-admins

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->createDepartment($name, $hidden);
        return $this->success("Department created");
    }

    public function updateDepartment($id, $name, $hidden = null) {
        /*

        Update an existing department.

        Parameters:
            - `id` (int): ID of the department
            - `name` (str): New name of the department
            - `hidden` (bool): Whether this department is hidden from non-admins

        Returns:
            array("success" => (bool), "message" => (str))

        */

        $this->db->updateDepartment($id, $name, $hidden);
        return $this->success("Department updated");
    }

    public function listDepartments() {
        /*

        List all departments.

        Returns:
            An array of zero or more associative arrays, representing departments. Department row format:
            array(
                "id" => (int),
                "name" => (str)
            )

        */

        return $this->db->listDepartments()->fetchAll();
    }

}

$api = new Department($db);
echo apiCall($api, $access, $role, $_POST);

?>
