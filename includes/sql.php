<?php
if (!defined('TRACKER')) die('No.');

/*---------------Start of SQL Config---------------*/
$host = "10.10.0.30";
$user = "blfc";
$sqlpass = "0m3cV37F0Cw4reYx";
$dataBase = "blfc_vtracker";

/*---------------End of SQL Config---------------*/

//New PDO Connection
$db = new PDO("mysql:host=$host;dbname=$dataBase;charset=utf8", "$user", "$sqlpass", array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

//Enable profiling
try {
    $db->query('SET profiling = 1');
} catch(PDOException $ex) {
    echo "<script>alert('Error enabling SQL Performance Profiling!');</script>";
}
?>