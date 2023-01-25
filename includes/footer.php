<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/20/2018
 * Time: 11:10 PM
 */

if (!defined('TRACKER')) die('No.');
?>

<script src="js/lib/popper.min.js"></script>
<script src="js/lib/bootstrap.bundle.min.js"></script>

<script src="js/lib/bootstrap-select.min.js"></script>

<?php
if ($devMode === 1) echo "<script>$(document).ready(function () {logoutTime = 60000})</script>";
?>

</body>
</html>