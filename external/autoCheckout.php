<?php
/**
 *
 * Finds anyone currently checked in and checks them out, crediting them for 1 hour.
 * To be ran from a cron job every night at 3:30am.
 *
 */

if (php_sapi_name() != 'cli') die('No.');

require "../main.php";

chdir(dirname(__FILE__));

echo "Checking out active clockins...\n";
foreach ($db->getActiveCheckIns() as $clockin) {
    $uid = $clockin['uid'];
    echo "Checking out: " . $uid . " > " . $clockin['checkin'] . "\n";
    $out = new DateTime("now");
    $in = new DateTime($clockin['checkin']);
    if (date_diff($in, $out)->h >= 1) {
        echo "...Checked in more than an hour ago.\n";
        $out = $in->add(new DateInterval('PT1H'));
    }

    $ret = $db->checkOut($uid, $out);
	print("\nCheckout Results: " . $ret['code'] . " > " . $ret['msg'] . "(" . $ret['diff'] . ")");
    $db->createNotification($uid, "danger", 0, "You were automatically checked out because you may have forgotten to check out. <br>You've been credited with 1 hour.<br><b>Please verify your time with your department lead or volunteer desk!</b>");
}