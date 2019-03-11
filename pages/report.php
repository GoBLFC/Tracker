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

    <link rel="stylesheet" media="all" href="/tracker/css/style.css">
    <link rel="stylesheet" type="text/css" href="/tracker/css/custom-css-bootstrap.css">

    <title><?php echo "$title" ?></title>
</head>

<?php
//print_r($unclocked);
//print_r($users);
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
            buttons: [
                'csv', 'excel', 'pdf'],
            <?php
            if ($type == "unclocked") {
                echo "\"order\": [[2, \"desc\"]]";
            } else if (strpos($type, 'vHour') !== false) {
                echo "\"order\": [[0, \"desc\"]]";
            }
            else if ($type == "logs") {
                echo "\"order\": [[2, \"desc\"]]";
            }
            ?>
        });
    });
</script>
</html>
