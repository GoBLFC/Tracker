<?php

require "main.php";

if (!$isAdmin) die('Unauthorized.');

$type = $_GET['type'];
$header = array();

$users = $db->listUsers();
$depts = $db->listDepartments(hidden: true);
$deptSummary = array();

if ($type == "unclocked") {
    //$title = "Unclocked Users for " . date('Y/m/d', strtotime("-1 days"));
    $title = "Latest Unclocked Users";
    $unclocked = $db->listUnclockedUsers();
    $header = array("ID", "Nickname", "Check-In", "Auto Checkout", "Dept");
} else if (strpos($type, 'vHour') !== false) {
    $hour = (int)filter_var($type, FILTER_SANITIZE_NUMBER_INT);
    $title = "Volunteers Worked $hour+ Hours";
    $header = array("ID", "Nickname", "Worked", "Earned");

    $newUsers = array();
    foreach ($users as $user) {
        $worked = getMinutesTotal($user['id']);
        $earned = calculateBonusTime($user['id'], false) + $worked;
        $user['worked'] = round($worked / 60, 2);
        $user['earned'] = round($earned/ 60, 2);

        if ($earned >= $hour * 60) $newUsers[$user['id']] = $user;
    }

    $users = $newUsers;
} else if ($type == "logs") {
    $title = "Staff Logs";
    $header = array("Badge", "Nickname", "Time", "Action", "Data");
    $logs = $db->getLogs();
} else if ($type == "depttimes") {
    $title = "Dept. Times";
    $header = array("Department", "Hours", "Unique", "Total Clockins");

    $entries = $db->getAllTrackerEntries();
    $users = $count = $times = array();

    foreach ($depts as $dept) {
        $hours[$dept['id']] = 0;
        $count[$dept['id']] = 0;
    }

    foreach ($entries as $entry) {
        if (!isset($users[$entry['dept']])) $users[$entry['dept']][$entry['uid']] = 0;
        if (!isset($users[$entry['dept']][$entry['uid']])) $users[$entry['dept']][$entry['uid']] = 0;
        if (!isset($times[$entry['dept']])) $times[$entry['dept']] = 0;

        $users[$entry['dept']][$entry['uid']]++;
        $users['total'][$entry['uid']] = 0;
        $times[$entry['dept']] += $entry['diff'];
        $count[$entry['dept']]++;
    }

    $totalTime = 0;
    $totalCheckins = 0;
    $rows = [];
    foreach ($depts as $dept) {
        if (!isset($users[$dept['id']])){
            $times[$dept['id']] = 0;
            $users[$dept['id']] = array();
        }

        $rows[] = [
            "name" => $dept["name"],
            "hours" => number_format((float)$times[$dept['id']] / 3600, 2, '.', ''),
            "unique" => sizeof($users[$dept['id']]),
            "clockins" => $count[$dept['id']]
        ];

        $totalTime += $times[$dept['id']];
        $totalCheckins += $count[$dept['id']];
    }

    $totalHours = number_format((float)$totalTime / 3600, 2, '.', '');
    $totalUnique = sizeof($users['total']);
} else if ($type == "apps") {
    $title = "Volunteer Applications";
    $header = ["ID", "Legal Name", "Roles", "Assigned?", "Staff?", "Contact Pref", "Email", "Phone", "Telegram", "Twitter", "Discord", "Hours Desired", "W", "R", "F", "S", "U", "M", "T", "P", "Can't Miss", "Comments", "Previous BLFC Experience", "Other con experience", "Created At", "Updated"];

    // Begin ConCat API requests
    $client = new GuzzleHttp\Client(["base_uri" => $OAUTH_CONCAT_BASE_URL]);

    // Get authorization
    $auth = $client->request("POST", "/api/oauth/token", [
        "form_params" => [
            "client_id" => $OAUTH_CLIENT_ID,
            "client_secret" => $OAUTH_CLIENT_SECRET,
            "grant_type" => "client_credentials",
            "scope" => "volunteer:read"
        ]
    ]);

    $bearer = json_decode($auth->getBody())->access_token;

    // Get volunteer apps
    $apps = [];

    // Get the first page of results, also primes the while-loop if there is more than one page.
    $request = $client->request("POST", "/api/v0/volunteers/search", [
        "headers" => [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer $bearer"
        ],
        // Specifying raw JSON as a string because Guzzle won't encode and send an empty array using the "json" key.
        // If you need to test pagination during development, you can replace the body here with {"limit": 1}.
        "body" => "{}"
    ]);

    $response = json_decode($request->getBody());
    $apps = array_merge($apps, $response->data);

    // Get additional pages (if any) until they run out.
    while ($response->hasMore) {
        $request = $client->request("POST", "/api/v0/volunteers/search", [
            "headers" => ["Authorization" => "Bearer $bearer"],
            "json" => ["nextPage" => $response->nextPage]
        ]);
        $response = json_decode($request->getBody());
        $apps = array_merge($apps, $response->data);
    }

    // Build array of all department names where user provided input
	$departments = [];
	foreach ($apps as $app) {
        foreach ($app->departments as $dept){
            $departments[] = $dept->name;
        }
	}
    $departments = array_unique($departments);
    sort($departments);

    // Append departments as table columns
    foreach ($departments as $dept) $header[] = $dept;

    $rows = [];

    foreach ($apps as $app) {
        $options = [];

        $row = [];

        $row["id"] = $app->user->id;
        $row["staff"] = $app->user->isStaff;
        $row["email"] = $app->user->email;
        $row["phone"] = $app->user->phone;
        $row["created"] = $app->createdAt;
        $row["updated"] = $app->updatedAt;

        $legalName = "{$app->user->firstName} {$app->user->lastName}";
        $preferredName = $app->user->preferredName;
        $row["name"] = ($preferredName) ? $preferredName . " ({$legalName})" : $legalName;

        $row["primaryContact"] = null;

        // Insert contact details as columns, also sets primary contact method
        foreach ($app->contactMethods as $contact) {
            if (in_array($contact->name, ["telegram", "twitter", "discord"])) {
                $row[$contact->name] = $contact->value;
                if (isset($contact->isPrimary) && $contact->isPrimary) {
                    $row["primaryContact"] = $contact->name;
                }
            }
        }

        // Build per-user list of departments and see if they're assigned to any
        $depts = [];
        $row["assigned"] = false;
        foreach ($app->departments as $dept) {
            $depts[] = $dept->name;
            foreach ($dept->states as $state) {
                if ($state == "assignment") {
                    $row["assigned"] = true;
                }
            }
        }
        $row["roles"] = implode(", ", $depts);

        // Get options
        foreach ($app->options as $option) {
            $opt = $option->name;
            switch ($opt) {
                case "Available Hours":
                    $row["availableHours"] = $option->value;
                    break;
                case "Volunteer Days":
                    $dows = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    foreach ($dows as $dow) {
                        if (in_array($dow, $option->value)) {
                            $row[mb_strtolower($dow)] = true;
                        } else {
                            $row[mb_strtolower($dow)] = false;
                        }
                    }
                    break;

                // The rest of this is pretty hacky because of how options are provided by the ConCat API.
                // Any slight change of wording or the removal of these options could break the report.
                // In other words: This assumes default ConCat volunteer application form fields are retained.

                // "Are you available to help out in the months before the con?"
                // This is a single-select option with two values: "yes" or "no"
                case (str_contains($opt, "help out") && str_contains($opt, "before the con")):
                    if ($option->value == "yes") {
                        $row["preCon"] = true;
                    } elseif ($option->value == "no") {
                        $row["preCon"] = false;
                    }
                    break;

                // "Are there any events you can not miss? [Optional]"
                // Freetext field, max length 4000
                case (str_contains($opt, "events") && str_contains($opt, "can not miss")):
                    $row["cantMiss"] = $option->value;
                    break;

                // "Is there anything else you would like to mention?"
                // Freetext field, max length 4000
                case (str_contains($opt, "anything else") && str_contains($opt, "like to mention")):
                    $row["anythingElse"] = $option->value;
                    break;

                // "Have you previously volunteered with <CON NAME>? If so, what did you do? [Optional]"
                // Freetext field, max length 4000
                case (str_contains($opt, "previously volunteered") && str_contains($opt, "what did you do")):
                    $row["previousVolunteer"] = $option->value;
                    break;

                // "Have you previously volunteered for another convention? If so, which? [Optional]"
                // Freetext field, max length 4000
                case (str_contains($opt, "previously volunteered") && str_contains($opt, "another convention")):
                    $row["previousVolunteerOther"] = $option->value;
                    break;
            }
        }

        $row["deptSelections"] = [];

        foreach ($departments as $dpt) {

            $stateText = "";

            foreach ($app->departments as $dept) {
                if ($dpt != $dept->name) continue;

                $stateText = "";
                foreach ($dept->states as $state) {
                    if ($state == "avoid") {
                        $stateText = "X";
                    } elseif ($state == "assignment") {
                        $stateText .= "✓";
                    } elseif ($state == "experience") {
                        $stateText .= "!";
                    } elseif ($state == "interest") {
                        $stateText .= "♥";
                    } else {
                        $stateText = "???";
                    }
                }
            }
            $row["deptSelections"][] = $stateText;
        }

        $rows[] = $row;
    }
}

$reportDetails = [
    "title" => $title,
    "header" => $header
];

if (isset($_GET["type"])) {
    $type = $_GET["type"];
    switch ($type) {
        case "unclocked":
            echo $twig->render("report/unclocked.html", array_merge($reportDetails, [
                "unclocked" => $unclocked,
                "orderCol" => 2
            ]));
            break;
        case strpos($type, 'vHour') !== false:
            echo $twig->render("report/vHour.html", array_merge($reportDetails, [
                "users" => $users,
                "orderCol" => 0
            ]));
            break;
        case "logs":
            echo $twig->render("report/logs.html", array_merge($reportDetails, [
                "logs" => $logs,
                "orderCol" => 2
            ]));
            break;
        case "depttimes":
            echo $twig->render("report/depttimes.html", array_merge($reportDetails, [
                "rows" => $rows,
                "totalHours" => $totalHours,
                "totalUnique" => $totalUnique,
                "totalCheckins" => $totalCheckins,
                "orderCol" => 1
            ]));
            break;
        case "apps":
            echo $twig->render("report/apps.html", array_merge($reportDetails, [
                "rows" => $rows,
                "orderCol" => 0
            ]));
            break;
        default:
            http_response_code(404);
    }
}

?>
