<?php

// TODO: Return method specific results instead of arrays

/* SQL Queries */

function getEligibleRewards($uid)
{
    global $db;
    // Current claims
    $earnedHours = (calculateBonusTime($uid, false) + getMinutesTotal($uid)) / 60;
    $claims = $db->listRewardClaims($uid);
    $rewards = $db->listRewards(hidden: false);

    $availRewards = [];
    foreach ($rewards as $reward) {
        //if ($reward['hours'] == 0) continue;

        $availRewards[$reward['id']] = $reward;
        $availRewards[$reward['id']]['claimed'] = false;
        $availRewards[$reward['id']]['avail'] = true;
        if ($earnedHours < $reward['hours']) $availRewards[$reward['id']]['avail'] = false;

        // Set claim state
        foreach ($claims as $claim) {
            //if ($claim['claim'] == $reward['id']) continue 2;
            if ($claim['claim'] == $reward['id']) $availRewards[$reward['id']]['claimed'] = true;
        }
    }

    return $availRewards;
}

function getClockTime($uid)
{
    global $db;
    $in = $db->getCheckIn($uid)->fetch();
    if (!$in) return -1;
    return time() - strtotime($in['check_in']);
}

function getMinutesToday($uid)
{
    global $db;
    $stmt = $db->conn->prepare("SELECT id,check_in,check_out FROM `tracker` WHERE `uid` = :uid AND (DATE(`check_in`) = CURDATE() OR DATE(`check_out`) = CURDATE() OR `check_out` IS NULL)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $time = 0;
    foreach ($periods as $period) {
        $checkout = $period['check_out'];
        if ($checkout == null) {
            $checkout = date("Y-m-d h:i:sa", time());
            //echo "Null: " . $period['id'] . ":" . $checkout;
        }
        $overlap = overlapInMinutes(date("Y-m-d 00:00:01"), date("Y-m-d 23:59:59"), $period['check_in'], $checkout);
        //echo $period['id'] . ":" . $overlap . "\n";
        $time = $time + $overlap;
    }

    return $time;
}

function getMinutesTotal($uid)
{
    global $db;
    $stmt = $db->conn->prepare("SELECT id,check_in,check_out FROM `tracker` WHERE `uid` = :uid");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $time = 0;
    foreach ($periods as $period) {
        $checkout = $period['check_out'];
        if ($checkout == null) $checkout = date("Y-m-d h:i:sa", time());
        $start_date = new DateTime($period['check_in']);
        $since_start = $start_date->diff(new DateTime($checkout));
        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;

        $time = $time + $minutes;
    }

    return $time;
}

// Somewhat inefficient O(N2) queries to get all bonus periods and find all time entries that reside within them.
function calculateBonusTime($uid, $array)
{
    global $db;
    $stmt = $db->conn->prepare("SELECT * FROM `bonuses`");
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bonus = 0;
    $entries = [];
    $debug = [];

    foreach ($periods as $period) {
        $stmt = $db->conn->prepare("SELECT * FROM `tracker` WHERE `uid` = :uid ORDER BY `check_in` ASC");
        //$stmt = $db->conn->prepare("SELECT * FROM `tracker` WHERE `dept` = :dept AND `uid` = :uid AND (`check_in` BETWEEN :start1 AND :stop1 OR `check_out` BETWEEN :start2 AND :stop2)");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        //$stmt->bindValue(':dept', $period['dept'], PDO::PARAM_INT);
        //$stmt->bindValue(':start1', $period['start'], PDO::PARAM_STR);
        //$stmt->bindValue(':start2', $period['start'], PDO::PARAM_STR);
        //$stmt->bindValue(':stop1', $period['stop'], PDO::PARAM_STR);
        //$stmt->bindValue(':stop2', $period['stop'], PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            // Department check
            $departments = explode(",", $period['dept']);
            if (!$array && !in_array($result['dept'], $departments)) continue;

            if ($result['check_out'] == null) {
                $checkout = date("Y-m-d h:i:sA", time());
            } else {
                $checkout = date("Y-m-d h:i:sA", strtotime($result['check_out']));
                $result['check_out'] = date("M d h:i:sA", strtotime($result['check_out']));
            }

            if (!isset($result['overlap'])) $result['overlap'] = 0;
            if (!isset($result['bonus'])) $result['bonus'] = 0;

            if (in_array($result['dept'], $departments)) {
                $overlap = overlapInMinutes($period['start'], $period['stop'], $result['check_in'], $checkout);
                $result['overlap'] += $overlap;
                $result['bonus'] += ($period['modifier'] * $overlap) - $overlap;
                //echo "<br>OVERLAP: " . $overlap;
            } else {
                //echo "<br>SKIP";
            }

            $debug[$result['id']] = $result;
            $bonus = $bonus + $result['bonus'];
            //$trackers[$period['dept']][] = $result;

            if ($array) {
                //$timestamp = strtotime($result['check_in']);
				$id = $result['id'];
				
                $worked = strtotime($checkout) - strtotime($result['check_in']);

                // Redo dates for short format
                $checkin = date("M d h:i:sA", strtotime($result['check_in']));

                if (!isset($entries[$id]['bonus'])) $entries[$id]['bonus'] = 0;
                if (!isset($entries[$id]['overlap'])) $entries[$id]['overlap'] = 0;

                $entries[$id]['id'] = $result['id'];
                $entries[$id]['dept'] = $result['dept'];
                $entries[$id]['auto'] = $result['auto'];
                $entries[$id]['worked'] = $worked;
                $entries[$id]['bonus'] += $result['bonus'];
                $entries[$id]['overlap'] += $result['overlap'];
                $entries[$id]['check_in'] = $checkin;
                $entries[$id]['check_out'] = $result['check_out'];
                $entries[$id]['notes'] = $result['notes'];
                $entries[$id]['ongoing'] = !$result['check_out'];
            }
        }
    }

    //echo json_encode($debug);

    return $array ? $entries : $bonus;
}

function overlapInMinutes($startDate1, $endDate1, $startDate2, $endDate2)
{
    $lastStart = $startDate1 >= $startDate2 ? $startDate1 : $startDate2;
    $lastStart = strtotime($lastStart);

    $firstEnd = $endDate1 <= $endDate2 ? $endDate1 : $endDate2;
    $firstEnd = strtotime($firstEnd);

    $overlap = floor(($firstEnd - $lastStart) / 60);

    return $overlap > 0 ? $overlap : 0;
}
