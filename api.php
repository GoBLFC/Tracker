<?php

header("Content-Type: application/json");

require "main.php";

function error($msg) {
    // Returns a simple error JSON object
    return json_encode(["success" => false, "message" => $msg]);
}

function apiCall($api, $method, $params) {
    // Calls and returns the provided method, if it exists
    // Otherwise returns a JSON object representing an error message
    if (!method_exists($api, $method)) {
        http_response_code(404);
        return error("Method does not exist");
    }
    return json_encode($api->{$method}($params));
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
    protected $badgeID;

    public function __construct($db, $badgeID) {
        $this->db = $db;
        $this->badgeID = $badgeID;
    }

    protected function success($msg) {
        return ["success" => true, "message" => $msg];
    }

    protected function error($msg) {
        return ["success" => false, "message" => $msg];
    }
}

?>
