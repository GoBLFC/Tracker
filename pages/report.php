<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 3/6/2019
 * Time: 11:12 PM
 */
define('TRACKER', TRUE);

require "../main.php";
include('../includes/header.php');

if (!isAdmin($badgeID)) die('Unauthorized.');
$type = $_GET['type'];
$header = array();

$users = getUsers();
$depts = getDepartments(true);
$logs = array();
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
        $user['worked'] = $worked;
        $user['earned'] = $earned;

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
    $logs = getAllTrackerEntries();
} else if ($type == "apps") {
    $title = "Volunteer Applications";
    //$header = array("ID", "Legal Name");
    $header = array("ID", "Legal Name", "Roles", "Assigned?", "Staff?", "", "Contact Pref", "Email", "Phone", "Telegram", "Twitter", "Discord", "2023 Hours Desired", "W", "R", "F", "S", "U", "M", "T", "P", "Can't Miss", "", "Comments", "Previous BLFC Experience", "Other con experience", "Created At", "Updated", "");

    $logs = getLogs();

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
} else {
    $title = $type;
}
?>

<html>
<head>
	<link rel="stylesheet" media="all" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap/custom-css-bootstrap.css">
	
    <script src="/js/lib/jquery-3.3.1.min.js"></script>

    <script src="/js/lib/datatables/jquery.dataTables.min.js"></script>
    <script src="/js/lib/datatables/dataTables.buttons.min.js"></script>
    <script src="/js/lib/datatables/buttons.flash.min.js"></script>
    <script src="/js/lib/datatables/jszip.min.js"></script>
    <script src="/js/lib/datatables/pdfmake.min.js"></script>
    <script src="/js/lib/datatables/vfs_fonts.js"></script>
    <script src="/js/lib/datatables/buttons.html5.min.js"></script>
    <script src="/js/lib/datatables/buttons.print.min.js"></script>


	
    <link rel="stylesheet" href="/css/datatables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="/css/datatables/buttons.dataTables.min.css"/>
	
    <title><?php echo "$title" ?></title>
</head>

<?php
//print_r($unclocked);
//print_r($users);

if ($type == "apps") {
    ?>

<?php
//echo  $_SESSION['accessToken'];
//print_r(getApps());
}
?>

<table id="tableDat" class="display nowrap" style="width:100%">
    <thead>
    <tr>
        <?php
        foreach ($header as $th) echo "<th>" . $th . "</th>";
        ?>
    </tr>
    </thead>
    <tbody style="color: black;">

    <?php
    if ($type == "unclocked") {
        foreach ($unclocked as $td) {
            echo "<tr>";
            echo "<td>" . $td['uid'] . "</td>";
            echo "<td>" . $users[$td['uid']]['nickname'] . "</td>";
            echo "<td>" . $td['checkin'] . "</td>";
            echo "<td>" . $td['checkout'] . "</td>";
            echo "<td>" . $depts[$td['dept']]['name'] . "</td>";
            echo "</tr>";
        }
    } else if (strpos($type, 'vHour') !== false) {
        foreach ($users as $td) {
            echo "<tr>";
            echo "<td>" . $td['id'] . "</td>";
            echo "<td>" . $td['nickname'] . "</td>";
            echo "<td>" . round($td['worked'] / 60, 2) . "</td>";
            echo "<td>" . round($td['earned'] / 60, 2) . "</td>";
            echo "</tr>";
        }
    } else if ($type == "logs") {
        foreach ($logs as $td) {
            echo "<tr>";
            echo "<td>" . $td['uid'] . "</td>";
            echo "<td>" . $users[$td['uid']]['nickname'] . "</td>";
            echo "<td>" . $td['time'] . "</td>";
            echo "<td>" . $td['action'] . "</td>";
            echo "<td>" . $td['data'] . "</td>";
            echo "</tr>";
        }
    } else if ($type == "depttimes") {
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
        foreach ($depts as $dept) {
            if (!isset($users[$dept['id']])){
                $times[$dept['id']] = 0;
                $users[$dept['id']] = array();
            }

            echo "<tr>";
            echo "<td>" . $dept['name'] . "</td>";
            echo "<td>" . number_format((float)$times[$dept['id']] / 3600, 2, '.', '') . "</td>";
            echo "<td>" . sizeof($users[$dept['id']]) . "</td>";
            echo "<td>" . $count[$dept['id']] . "</td>";
            echo "</tr>";

            $totalTime += $times[$dept['id']];
            $totalCheckins += $count[$dept['id']];
        }

        echo "Total Hours: " . number_format((float)$totalTime / 3600, 2, '.', '');
        echo " | Total Unique: " . sizeof($users['total']);
        echo " | Total Checkins: " . $totalCheckins;
    } else if ($type == "apps") {
		foreach ($dataArr as $appData){
			foreach ($appData->data as $td) {
				$contacts = array();
				$options = array();

				if ($td->user->id == 14378) continue;
				echo "<tr>";
				echo "<td>" . $td->user->id . "</td>";
				echo "<td>";
				echo ($td->user->preferredName) ? $td->user->preferredName . " (" . $td->user->firstName . " " . $td->user->lastName . ")" : $td->user->firstName . " " . $td->user->lastName;
				echo "</td>";
				//echo "<td>" . ((isset($td->user->registration->badgeName)) ? $td->user->registration->badgeName : "(UNREGISTERED)") . "</td>";
				echo "<td>";
				foreach ($td->departments as $dept) {
					echo $dept->name . ", ";
				}
				echo "</td>";
				$assigned = "No";
				foreach ($td->departments as $dept){
					foreach ($dept->states as $state) {
						if ($state == "assignment") {
							$assigned = "Yes";
						}
					}
				}
				
				echo "<td>" . $assigned . "</td>";
				echo "<td>" . (($td->user->isStaff) ? "Yes" : "No") . "</td>";
				echo "<td></td>";
				
				$primaryContact = "";
				foreach ($td->contactMethods as $contact){
					$contacts[$contact->name]['value'] = $contact->value;
					$contacts[$contact->name]['isPrimary'] = $contact->isPrimary;
					if ($contact->isPrimary) $primaryContact = $contact->name;
				}
				
				echo "<td>" . $primaryContact  . "</td>";
				echo "<td>" . $td->user->email . "</td>";
				echo "<td>" . $td->user->phone . "</td>";
				echo "<td>" . $contacts['telegram']['value'] . "</td>";
				echo "<td>" . $contacts['twitter']['value'] . "</td>";
				echo "<td>" . $contacts['discord']['value'] . "</td>";
				#echo "<td>" . $td->contactMethodFacebook . "</td>";

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
				
				echo "<td>" . $options['Available Hours'] . "</td>";
				echo "<td>";

				if (in_array("Wednesday", $options['Volunteer Days'])) echo "W";
				echo "</td>";
				echo "<td>";
				if (in_array("Thursday", $options['Volunteer Days'])) echo "R";
				echo "</td>";
				echo "<td>";
				if (in_array("Friday", $options['Volunteer Days'])) echo "F";
				echo "</td>";
				echo "<td>";
				if (in_array("Saturday", $options['Volunteer Days'])) echo "S";
				echo "</td>";
				echo "<td>";
				if (in_array("Sunday", $options['Volunteer Days'])) echo "U";
				echo "</td>";
				echo "<td>";
				if (in_array("Monday", $options['Volunteer Days'])) echo "M";
				echo "</td>";
				echo "<td>";
				if (in_array("Tuesday", $options['Volunteer Days'])) echo "T";
				echo "</td>";
				echo "<td>";
				echo $options['Are you available to help out in the months before the con?'] . "</td>";
				echo "</td>";
				echo "<td>" . $options['Are there any events you can not miss? [Optional]'] . "</td>";
				echo "<td></td>";
				echo "<td>" . $options['Is there anything else you would like to mention?'] . "</td>";
				echo "<td>" . $options['Have you previously volunteered with Biggest Little Fur Con 2023? If so, what did you do? [Optional]'] . "</td>";
				echo "<td>" . $options['Have you previously volunteered for another convention? If so, which? [Optional]'] . "</td>";
				echo "<td>" . $td->createdAt . "</td>";
				echo "<td>" . $td->updatedAt . "</td>";

				echo "<td></td>";
				foreach ($departments as $dpt){
					echo "<td>";

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
							} else{
								$stateText = "???";
							}
						}
						
						echo "<div>$stateText</div>";
					}
					
					echo "</td>";
				}

				echo "</tr>";
			}
		}
    }
    ?>

    </tbody>
    <tfoot>
    <tr>
        <?php
        foreach ($header as $th) echo "<th>" . $th . "</th>";
        ?>
    </tr>
    </tfoot>
</table>

<script>
    $(document).ready(function () {
        $('#tableDat').DataTable({
            dom: 'Bfrtip',
            "pageLength": 50,
            buttons: [
                'csv', 'excel', 'pdf'],
            <?php
            if ($type == "unclocked") {
                echo "\"order\": [[2, \"desc\"]]";
            } else if (strpos($type, 'vHour') !== false) {
                echo "\"order\": [[0, \"desc\"]]";
            } else if ($type == "logs") {
                echo "\"order\": [[2, \"desc\"]]";
            } else if ($type == "apps") {
                echo "\"order\": [[0, \"desc\"]]";
            } else if ($type == "depttimes") {
                echo "\"order\": [[1, \"desc\"]]";
            }
            ?>
        });
    });
</script>
</html>
