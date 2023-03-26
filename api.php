<?php

header("Content-Type: application/json");

require "main.php";

function error($msg) {
    // Returns a simple error JSON object
    return json_encode(["success" => false, "message" => $msg]);
}

function apiCall($api, $access, $role, $request) {
    // Calls and returns the provided method, if it exists and user has permission
    // Otherwise returns a JSON object representing an error message

    $method = $request["func"];
    unset($request["func"]);
    $params = $request;

    if (!method_exists($api, $method)) {
        http_response_code(404);
        return error("Method does not exist");
    }

    // Look up required user role for function
    // If one is not found, the function defaults to access denied for everyone
    if (!array_key_exists($method, $access) || $access[$method]->value > $role) {
        http_response_code(403);
        return error("Access denied");
    }

    return json_encode($api->{$method}(...$params));
}

if ($user == null) {
    http_response_code(401);
    echo error("Not authenticated");
    exit();
} else if (!isset($_POST["func"])) {
    http_response_code(400);
    echo error("No method specified");
    exit();
} else if (!($isManager || $isAdmin)) {
    if (!($db->getDevMode() || $db->checkKiosk($_COOKIE["kiosknonce"])->fetch())) {
        http_response_code(403);
        echo error("Kiosk not authorized");
        exit();
    } else if (!$db->getSiteStatus()) {
        http_response_code(403);
        echo error("Site is disabled");
        exit();
    }
}

class API {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    protected function success($msg) {
        return ["success" => true, "message" => $msg];
    }

    protected function error($msg) {
        http_response_code(400);
        return ["success" => false, "message" => $msg];
    }
}

?>
