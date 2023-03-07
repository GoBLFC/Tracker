<?php
if (!defined('TRACKER')) die('No.');

require ROOT_DIR . "/config.php";

//New PDO Connection
$db = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USERNAME, $DB_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

//Enable profiling
try {
    $db->query('SET profiling = 1');
} catch(PDOException $ex) {
    echo "<script>alert('Error enabling SQL Performance Profiling!');</script>";
}
?>