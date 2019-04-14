<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/19/2019
 * Time: 10:52 PM
 *
 * Finds anyone currently checked in and checks them out, crediting them for 1 hour.
 * To be ran from a cron job every night at 3:30am.
 *
 */

if (php_sapi_name() != 'cli') die('No.');
define('TRACKER', TRUE);

chdir(dirname(__FILE__));
include('../includes/sql.php');
include('../includes/functions.php');

echo "Checking out active clockins...\n";
foreach (getActiveClockins() as $clockin) {
    $uid = $clockin['uid'];
    echo "Checking out: " . $uid . " > " . $clockin['checkin'] . "\n";
    $out = new DateTime("now");
    $in = new DateTime($clockin['checkin']);
    if (date_diff($in, $out)->h >= 1) {
        echo "...Checked in more than an hour ago.\n";
        $out = $in->add(new DateInterval('PT1H'));
    }

    checkOut($uid, $out, null);
    createNotification($uid, "danger", "You were automatically checked out because you may have forgotten to check out. <br>You've been credited with 1 hour.<br><b>Please verify your time with your department lead or volunteer desk!</b>");
}