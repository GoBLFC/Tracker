<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 3/6/2019
 * Time: 11:12 PM
 */
define('TRACKER', TRUE);
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
    $header = array("ID", "Legal Name", "Badge Name", "Roles", "Assigned?", "", "Contact Pref", "Email", "Phone", "Telegram", "Twitter", "Facebook", "E-Contacts", "", "2018 Hours Recorded", "2019 Hours Desired", "W", "R", "F", "S", "U", "M", "T", "P", "Can't Miss", "", "Comments", "Previous BLFC Experience", "Other con experience", "Created At", "Updated", "");

    $logs = getLogs();

    $data = json_decode($_POST['appdata']);

    $departments = array();
    foreach ($data as $td) {
        foreach ($td->volunteerDepartments as $dept) {
            $depname = $dept->department->name;
            if (array_search($depname, $departments) === FALSE) {
                $departments[] = $depname;
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
    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.4/js/buttons.print.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.4/css/buttons.dataTables.min.css"/>

    <link rel="stylesheet" media="all" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/custom-css-bootstrap.css">

    <title><?php echo "$title" ?></title>
</head>

<?php
//print_r($unclocked);
//print_r($users);

if ($type == "apps") {
    ?>
    <form method="post" action="">
        <div class="form-group">
            <input type="text" class="form-control" id="appdata" name="appdata"
                   placeholder="Application Data">
            <small id="emailHelp" class="form-text text-muted">Copy paste data from <a
                        href="https://reg.goblfc.org/api/volunteers" target="_blank">here</a></small>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
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
        $users = array();
        $count = array();
        $times = array();

        foreach ($depts as $dept) {
            $hours[$dept['id']] = 0;
            $count[$dept['id']] = 0;
        }

        foreach ($entries as $entry) {
            if (!isset($users[$entry['uid']])) $users[$entry['uid']] = 0;
            $users[$entry['dept']][$entry['uid']]++;
            $users['total'][$entry['uid']]++;
            $times[$entry['dept']] += $entry['diff'];
            $count[$entry['dept']]++;
        }

        $totalTime = 0;
        $totalCheckins = 0;
        foreach ($depts as $dept) {
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
        if (isset($_POST['appdata'])) {
            $data = str_replace("<", "&lt;", $_POST['appdata']);
            $data = str_replace(">", "&gt;", $data);
            $data = json_decode($data);

            foreach ($data as $td) {
                if ($td->userId == 14378) continue;
                echo "<tr>";
                echo "<td>" . $td->userId . "</td>";
                echo "<td>";
                echo ($td->user->preferredName) ? $td->user->preferredName . " (" . $td->user->firstName . " " . $td->user->lastName . ")" : $td->user->firstName . " " . $td->user->lastName;
                echo "</td>";
                echo "<td>" . ((isset($td->user->registration->badgeName)) ? $td->user->registration->badgeName : "(UNREGISTERED)") . "</td>";
                echo "<td>";
                foreach ($td->user->roles as $role) {
                    echo $role->name . ", ";
                }
                echo "</td>";
                $assigned = "No";
                foreach ($td->volunteerDepartments as $tddept) {
                    if ($tddept->type == "assignment") {
                        $assigned = "Yes";
                    }
                }
                echo "<td>" . $assigned . "</td>";
                echo "<td></td>";
                echo "<td>" . $td->contactMethod . "</td>";
                echo "<td>" . $td->user->email . "</td>";
                echo "<td>" . $td->user->phone . "</td>";
                echo "<td>" . $td->contactMethodTelegram . "</td>";
                echo "<td>" . $td->contactMethodTwitter . "</td>";
                echo "<td>" . $td->contactMethodFacebook . "</td>";

                echo "<td>";
                if (isset($td->user->registration->emergencyContactName1)) {
                    echo $td->user->registration->emergencyContactName1 . ": " . $td->user->registration->emergencyContactPhone1;
                }
                if (isset($td->user->registration->emergencyContactName2)) {
                    echo " | " . $td->user->registration->emergencyContactName2 . ": " . $td->user->registration->emergencyContactPhone2;
                }
                echo "</td>";

                echo "<td></td>";
                echo "<td>";
                foreach ($td->user->previousVolunteers as $hours) {
                    if ($hours->conventionId == "2018") echo $hours->workedHours;
                }
                echo "</td>";
                echo "<td>" . $td->availableHours . "</td>";
                echo "<td>";
                if ($td->availableDaysWednesday) echo "W";
                echo "</td>";
                echo "<td>";
                if ($td->availableDaysThursday) echo "R";
                echo "</td>";
                echo "<td>";
                if ($td->availableDaysFriday) echo "F";
                echo "</td>";
                echo "<td>";
                if ($td->availableDaysSaturday) echo "S";
                echo "</td>";
                echo "<td>";
                if ($td->availableDaysSunday) echo "U";
                echo "</td>";
                echo "<td>";
                if ($td->availableDaysMonday) echo "M";
                echo "</td>";
                echo "<td>";
                if ($td->availableDaysTuesday) echo "T";
                echo "</td>";
                echo "<td>";
                if ($td->availableBeforeCon) echo "P";
                echo "</td>";
                echo "<td>" . $td->eventsCanNotMiss . "</td>";
                echo "<td></td>";
                echo "<td>" . $td->anythingElse . "</td>";
                echo "<td>" . $td->previousConExperience . "</td>";
                echo "<td>" . $td->previousOtherExperience . "</td>";
                echo "<td>" . $td->createdAt . "</td>";
                echo "<td>" . $td->updatedAt . "</td>";

                echo "<td></td>";
                foreach ($departments as $dept) {
                    echo "<td>";
                    $state = "";
                    foreach ($td->volunteerDepartments as $tddept) {
                        if ($dept == $tddept->department->name) {
                            if ($tddept->type == "avoid" OR $state == "X") {
                                $state = "X";
                            } else if ($tddept->type == "assignment" or $state == "✓") {
                                $state = "✓";
                            } else if ($tddept->type == "experience") {
                                $state .= "!";
                            } else if ($tddept->type == "interest") {
                                $state .= "♥";
                            }
                        }
                    }
                    echo $state;
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

<div class="container" style="top: 5em;">
    <div class="card">
        <div class="row">
            <div class="col-sm">
                <div class="card-body">
                    <div class="card">
                        <div class="card-header cadHeader">
                            <div>Dept Count</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <table id="asdasd" class="table table table-striped table">
                                        <thead>
                                        <tr>
                                            <th scope="col">Dept</th>
                                            <th scope="col">Count</th>
                                            <th scope="col">Hours</th>
                                        </tr>
                                        </thead>
                                        <tbody id="uRow">
                                        <?php
                                        foreach ($data as $td) {
                                            foreach ($departments as $dept) {
                                                if (!isset($deptSummary[$dept])) {
                                                    $deptSummary[$dept]['count'] = 0;
                                                    $deptSummary[$dept]['hours'] = 0;
                                                }

                                                foreach ($td->volunteerDepartments as $tddept) {
                                                    if ($dept == $tddept->department->name) {
                                                        if ($tddept->type == "assignment" or $state == "✓") {
                                                            $deptSummary[$dept]['count']++;
                                                            $deptSummary[$dept]['hours'] += $td->availableHours;
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        foreach ($deptSummary as $dept => $key) {
                                            echo "<tr>";
                                            echo "<th>$dept</th>";
                                            echo "<td>" . $deptSummary[$dept]['count'] . "</td>";
                                            echo "<td>" . $deptSummary[$dept]['hours'] . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
