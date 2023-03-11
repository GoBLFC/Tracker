<?php

require "main.php";

if (!$isAdmin) die('Unauthorized.');

$type = $_GET['type'];
$header = array();

$users = getUsers();
$depts = getDepartments(true);
$deptSummary = array();

if ($type == "unclocked") {
    //$title = "Unclocked Users for " . date('Y/m/d', strtotime("-1 days"));
    $title = "Latest Unclocked Users";
    $unclocked = getUnclockedUsers();
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
    $logs = getLogs();
} else if ($type == "depttimes") {
    $title = "Dept. Times";
    $header = array("Department", "Hours", "Unique", "Total Clockins");

    $entries = getAllTrackerEntries();
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
    //$header = array("ID", "Legal Name");
    $header = array("ID", "Legal Name", "Roles", "Assigned?", "Staff?", "", "Contact Pref", "Email", "Phone", "Telegram", "Twitter", "Discord", "2023 Hours Desired", "W", "R", "F", "S", "U", "M", "T", "P", "Can't Miss", "", "Comments", "Previous BLFC Experience", "Other con experience", "Created At", "Updated", "");

    //$data = json_decode($_POST['appdata']);
	//echo getApps();
	
	// Get all pages of data
	$allData = false;
	$nextPage = "";
	$dataArr = array();
	while ($allData == false){
		$pageData = getApps($nextPage);
		$data = json_decode($pageData);

        // Pagination
		if ($data->hasMore){
			$nextPage = $data->nextPage;
		}else{
			$allData = true;
		}
		
		$dataArr[] = $data;
	}

    // Combine Pagination
	//$appData = array_merge([], ...$dataArr);
	
	//print_r($appData);
	
	$departments = array();
	foreach ($dataArr as $appData){
		foreach ($appData->data as $td) {
			foreach ($td->departments as $dept){
				$depname = $dept->name;
				if (array_search($depname, $departments) === FALSE) {
					$departments[] = $depname;
				}
			}
		}
	}

    sort($departments);

    foreach ($departments as $dept) array_push($header, $dept);

    $rows = [];

    foreach ($dataArr as $appData) {
        foreach ($appData->data as $td) {
            $contacts = array();
            $options = array();

            $row = [];

            $row["id"] = $td->user->id;
            $row["name"] = ($td->user->preferredName) ? $td->user->preferredName . " (" . $td->user->firstName . " " . $td->user->lastName . ")" : $td->user->firstName . " " . $td->user->lastName;
            //$row["badge"] = ((isset($td->user->registration->badgeName)) ? $td->user->registration->badgeName : "(UNREGISTERED)");

            $deptNames = [];
            foreach ($td->departments as $dept) {
                $deptNames[] = $dept->name;
            }
            $row["roles"] = implode(", ", $deptNames);

            $assigned = "No";
            foreach ($td->departments as $dept){
                foreach ($dept->states as $state) {
                    if ($state == "assignment") {
                        $assigned = "Yes";
                    }
                }
            }

            $row["assigned"] = $assigned;
            $row["staff"] = ($td->user->isStaff) ? "Yes" : "No";

            $primaryContact = "";
            foreach ($td->contactMethods as $contact){
                $contacts[$contact->name]['value'] = $contact->value;
                $contacts[$contact->name]['isPrimary'] = $contact->isPrimary;
                if ($contact->isPrimary) $primaryContact = $contact->name;
            }

            $row["primaryContact"] = $primaryContact;
            $row["email"] = $td->user->email;
            $row["phone"] = $td->user->phone;
            $row["telegram"] = $contacts['telegram']['value'];
            $row["twitter"] = $contacts['twitter']['value'];
            $row["discord"] = $contacts['discord']['value'];

            #echo "<td>";
            #if (isset($td->user->registration->emergencyContactName1)) {
            #    echo $td->user->registration->emergencyContactName1 . ": " . $td->user->registration->emergencyContactPhone1;
            #}
            #if (isset($td->user->registration->emergencyContactName2)) {
            #    echo " | " . $td->user->registration->emergencyContactName2 . ": " . $td->user->registration->emergencyContactPhone2;
            #}
            #echo "</td>";

            // Instantiate some options incase concat doesn't provide.
            $options['Volunteer Days'] = array();

            foreach ($td->options as $option){
                $options[$option->name] = $option->value;
            }

            $row["availableHours"] = $options["Available Hours"];

            if (in_array("Wednesday", $options['Volunteer Days'])) $row["wednesday"] = "W";
            if (in_array("Thursday", $options['Volunteer Days'])) $row["thursday"] = "R";
            if (in_array("Friday", $options['Volunteer Days'])) $row["friday"] = "F";
            if (in_array("Saturday", $options['Volunteer Days'])) $row["saturday"] = "S";
            if (in_array("Sunday", $options['Volunteer Days'])) $row["sunday"] = "U";
            if (in_array("Monday", $options['Volunteer Days'])) $row["monday"] = "M";
            if (in_array("Tuesday", $options['Volunteer Days'])) $row["tuesday"] = "T";

            $row["precon"] = $options['Are you available to help out in the months before the con?'];
            $row["cantMiss"] = $options['Are there any events you can not miss? [Optional]'];
            $row["anythingElse"] = $options['Is there anything else you would like to mention?'];
            $row["previousVolunteer"] = $options['Have you previously volunteered with Biggest Little Fur Con 2023? If so, what did you do? [Optional]'];
            $row["previousVolunteerOther"] = $options['Have you previously volunteered for another convention? If so, which? [Optional]'];
            $row["created"] = $td->createdAt;
            $row["updated"] = $td->updatedAt;

            $row["deptSelections"] = [];

            foreach ($departments as $dpt) {

                $stateText = "";

                foreach ($td->departments as $dept) {
                    if ($dpt != $dept->name) continue;

                    $stateText = "";
                    foreach ($dept->states as $state) {
                        if ($state == "avoid") {
                            $stateText = "X";
                        } else if ($state == "assignment") {
                            $stateText .= "✓";
                        } else if ($state == "experience") {
                            $stateText .= "!";
                        } else if ($state == "interest") {
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
