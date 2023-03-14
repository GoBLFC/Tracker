<?php

function isValidSession($session, $badge)
{
    global $db;
    if ($badge == "") return false;
    $user = $db->getUser($badge)->fetch();
    if ($user == null) return false;
    if ($user['id'] != $badge) return false;
    return $user;
}

// TODO: Return method specific results instead of arrays

/* SQL Queries */

//Insert or update - excessive binds are because you can't re-use values, silly.
function updateSession($id, $fName, $lName, $nName, $session)
{
    global $db;
    $stmt = $db->conn->prepare("INSERT INTO `users` (`id`, `first_name`, `last_name`, `nickname`, `admin`, `manager`, `last_session`, `last_ip`, `registered`, `reg_ua`, `tg_uid`) VALUES (:id, :fName, :lName, :nName, 0, 0, :lastsession, :lastip, NOW(), :regua, :tgid) ON DUPLICATE KEY UPDATE `first_name` = :fName2, `last_name` = :lName2, `nickname` = :nName2, `last_session` = :lastsession2, `last_ip` = :lastip2, `last_login` = NOW(), `tg_quickcode` = NULL");
    //$stmt = $db->conn->prepare("UPDATE `users` SET `last_session` = :lastsession, `last_ip` = :lastip WHERE `users`.`id` = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':fName', $fName, PDO::PARAM_STR);
    $stmt->bindValue(':fName2', $fName, PDO::PARAM_STR);
    $stmt->bindValue(':lName', $lName, PDO::PARAM_STR);
    $stmt->bindValue(':lName2', $lName, PDO::PARAM_STR);
    $stmt->bindValue(':nName', $nName, PDO::PARAM_STR);
    $stmt->bindValue(':nName2', $nName, PDO::PARAM_STR);
    $stmt->bindValue(':tgid', guidv4(openssl_random_pseudo_bytes(16)), PDO::PARAM_STR);
    $stmt->bindValue(':lastsession', $session, PDO::PARAM_STR);
    $stmt->bindValue(':lastsession2', $session, PDO::PARAM_STR);
    $stmt->bindValue(':regua', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
    $stmt->bindValue(':lastip', $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
    $stmt->bindValue(':lastip2', $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
    $stmt->execute();
}

function createUser($badgeID)
{
    global $db;
    $user = $db->getUser($badgeID)->fetch();
    if (count($user) > 0) {
        return 2;
    }

    global $db;
    $stmt = $db->conn->prepare("INSERT INTO `users` (`id`, `first_name`, `last_name`, `nickname`, `tg_uid`) VALUES (:id, 'TempUser', 'TempUser', 'TempUser', :tgid)");
    $stmt->bindValue(':id', $badgeID, PDO::PARAM_INT);
    $stmt->bindValue(':tgid', guidv4(openssl_random_pseudo_bytes(16)), PDO::PARAM_STR);
    $stmt->execute();

    return 1;
}

function setBanned($badgeID, $state)
{
    global $db;
    $user = $db->getUser($badgeID)->fetch();
    if ($state == 1 && !isset($user)) {
        updateSession($badgeID, "BAN", "BAN", "BAN", "BAN");
    }

    global $db;
    $stmt = $db->conn->prepare("UPDATE `users` SET `banned` = :state WHERE `id` = :id");
    $stmt->bindValue(':id', $badgeID, PDO::PARAM_INT);
    $stmt->bindValue(':state', $state, PDO::PARAM_INT);
    $stmt->execute();

    return isset($user) ? $user['nickname'] : "Unknown";
}

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

function checkOut($uid, $autoTime)
{
    global $db;
    $checkIn = $db->getCheckIn($uid)->fetch();
    $timeDiff = (new DateTime('NOW'))->getTimestamp() - (new DateTime($checkIn['checkin']))->getTimestamp();
    $ret = [];
    $ret['in'] = $checkIn;
    $ret['diff'] = $timeDiff;

    if ($timeDiff < 10) {
        if (!isset($_SESSION['quickclock'])) $_SESSION['quickclock'] = 0;
        $_SESSION['quickclock']++;

        if ($_SESSION['quickclock'] >= 20) {
            $ret['code'] = -2;
            if ($_SESSION['quickclock'] == 20) $ret['code'] = -3;
            $ret['msg'] = "LOOK AT WHAT YOU'VE DONE!!!!!11 >:(";
        } else if ($_SESSION['quickclock'] >= 10) {
            $ret['code'] = -2;
            $ret['msg'] = "SLOW DOWN COWBOY! SITE IS GETTING WARM!";
        } else {
            $ret['code'] = -1;
            $ret['msg'] = "Too quick! Please wait a moment...";
        }
    } else {
        $db->checkOut($uid, $autoTime);
        $ret['code'] = 1;
        $ret['msg'] = "Clocked out.";
    }

    return $ret;
}

function getClockTime($uid)
{
    global $db;
    $in = $db->getCheckIn($uid)->fetch();
    if (!$in) return -1;
    return time() - strtotime($in['checkin']);
}

function getMinutesToday($uid)
{
    global $db;
    $stmt = $db->conn->prepare("SELECT id,checkin,checkout FROM `tracker` WHERE `uid` = :uid AND (DATE(`checkin`) = CURDATE() OR DATE(`checkout`) = CURDATE() OR `checkout` IS NULL)");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $time = 0;
    foreach ($periods as $period) {
        $checkout = $period['checkout'];
        if ($checkout == null) {
            $checkout = date("Y-m-d h:i:sa", time());
            //echo "Null: " . $period['id'] . ":" . $checkout;
        }
        $overlap = overlapInMinutes(date("Y-m-d 00:00:01"), date("Y-m-d 23:59:59"), $period['checkin'], $checkout);
        //echo $period['id'] . ":" . $overlap . "\n";
        $time = $time + $overlap;
    }

    return $time;
}

function getMinutesTotal($uid)
{
    global $db;
    $stmt = $db->conn->prepare("SELECT id,checkin,checkout FROM `tracker` WHERE `uid` = :uid");
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $time = 0;
    foreach ($periods as $period) {
        $checkout = $period['checkout'];
        if ($checkout == null) $checkout = date("Y-m-d h:i:sa", time());
        $start_date = new DateTime($period['checkin']);
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
    $stmt = $db->conn->prepare("SELECT * FROM `time_mod`");
    $stmt->execute();
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bonus = 0;
    $entries = [];
    $debug = [];

    foreach ($periods as $period) {
        $stmt = $db->conn->prepare("SELECT * FROM `tracker` WHERE `uid` = :uid ORDER BY `checkin` ASC");
        //$stmt = $db->conn->prepare("SELECT * FROM `tracker` WHERE `dept` = :dept AND `uid` = :uid AND (`checkin` BETWEEN :start1 AND :stop1 OR `checkout` BETWEEN :start2 AND :stop2)");
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

            if ($result['checkout'] == null) {
                $checkout = date("Y-m-d h:i:sA", time());
            } else {
                $checkout = date("Y-m-d h:i:sA", strtotime($result['checkout']));
                $result['checkout'] = date("M d h:i:sA", strtotime($result['checkout']));
            }

            if (!isset($result['overlap'])) $result['overlap'] = 0;
            if (!isset($result['bonus'])) $result['bonus'] = 0;

            if (in_array($result['dept'], $departments)) {
                $overlap = overlapInMinutes($period['start'], $period['stop'], $result['checkin'], $checkout);
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
                //$timestamp = strtotime($result['checkin']);
				$id = $result['id'];
				
                $worked = strtotime($checkout) - strtotime($result['checkin']);

                // Redo dates for short format
                $checkin = date("M d h:i:sA", strtotime($result['checkin']));

                if (!isset($entries[$id]['bonus'])) $entries[$id]['bonus'] = 0;
                if (!isset($entries[$id]['overlap'])) $entries[$id]['overlap'] = 0;

                $entries[$id]['id'] = $result['id'];
                $entries[$id]['dept'] = $result['dept'];
                $entries[$id]['auto'] = $result['auto'];
                $entries[$id]['worked'] = $worked;
                $entries[$id]['bonus'] += $result['bonus'];
                $entries[$id]['overlap'] += $result['overlap'];
                $entries[$id]['checkin'] = $checkin;
                $entries[$id]['checkout'] = $result['checkout'];
                $entries[$id]['notes'] = $result['notes'];
                $entries[$id]['ongoing'] = !$result['checkout'];
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

function userSignIn($badgeID, $firstName, $lastName, $username)
{
    global $db;
	session_regenerate_id();

	$_SESSION['badgeid'] = $badgeID;

	setcookie("badge", $badgeID, 0, "/");
	setcookie("session", session_id(), 0, "/");
	if (isset($isAdmin) && ($isAdmin || $isManager)) $db->createLog($badgeID, "logIn", "ip:" . $_SERVER["REMOTE_ADDR"]);
	//die(print_r($userInfo));
	updateSession($badgeID, $firstName, $lastName, $username, session_id());
	
	return session_id();
}

function getToken(){
    global $OAUTH_CLIENT_ID;
    global $OAUTH_CLIENT_SECRET;
    global $OAUTH_CONCAT_BASE_URL;
	$data = array(
		'client_id' => $OAUTH_CLIENT_ID,
		'client_secret' => $OAUTH_CLIENT_SECRET,
		'grant_type' => 'client_credentials',
		'scope' => 'volunteer:read',
	);
	$req = jwt_request("{$OAUTH_CONCAT_BASE_URL}/api/oauth/token", $_SESSION['accessToken'], $data, true);
	return $req;
}

function getApps($nextPage)
{
    global $OAUTH_CONCAT_BASE_URL;
	if ($nextPage != ""){
		$data = json_encode(array(
			"nextPage"  => $nextPage,
		));
	}else{
		$data = "{}";
	}
		
    return jwt_request("{$OAUTH_CONCAT_BASE_URL}/api/v0/volunteers/search", json_decode(getToken())->access_token, $data, false);
}

function jwt_request($url, $token, $post, $content)
{
    //header('Content-Type: application/json'); // Specify the type of data
    $ch = curl_init($url); // Initialise cURL
    $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
    //echo $authorization;
	
	if ($content){
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); // Set the posted fields
    }else{
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization)); // Inject the token into the header
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
	}
	
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    $result = curl_exec($ch); // Execute the cURL statement
    curl_close($ch); // Close the cURL connection
	//print $result;
    return $result; // Return the received data
}

function guidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}