<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/20/2018
 * Time: 11:07 PM
 */
if (!defined('TRACKER')) die('No.');

$kiosk = checkKiosk($kiosksession);

// Validate posted login details
$lastName = $badgeID = "";
if (isset($_POST['badgeID']) || isset($_POST['lastName'])) {
    $badgeID = $_POST['badgeID'];
    $lastName = $_POST['lastName'];

    if ($badgeID == "") $message = "Please enter your badge ID.";
    if ($lastName == "") $message = "Please enter your last name.";
    if ($badgeID == "" && $lastName == "") $message = "Please enter the information below.";

    // Kiosk Authorization
    if ($badgeID == "owo" && $lastName == "IAMAKIOSK") {
        if (!empty($kiosk)) {
            $message = "Kiosk already authorized!";
        } else {
            $message = "KIOSK AUTHORIZED!<br><small>Refreshing...</small><script>
                            setTimeout(function () {
                                window.location.href = \"/tracker/\";
                            }, 2000);</script>";
            authorizeKiosk($session);
        }
    } else {
        if (empty($kiosk)) {
            $message = "Session not authorized.";
        } else {
            // Basic badge validation
            if ($badgeID != "" && $lastName != "") {
                $badgeIDclean = str_replace(["-", "–"], '', $badgeID);
                if (!is_numeric($badgeIDclean) || strlen($badgeIDclean) != 4) {
                    $message = "Invalid badge ID.";
                } else {
                    // Check and or create user

                    // Badge ID found
                    if (getUserByID($badgeID)) {
                        // Name matches?
                        if (validateUser($badgeIDclean, $lastName)) {
                            // Good login -- update session
                            $message = "Good login!";
                            include('landing.php');
                            die();
                        } else {
                            // Bad name
                            $message = "Details do not match.";
                        }
                    } else {
                        // Not found
                        $message = "User not found... creating. (go to landing page with double auth)";
                        if (!isset($_POST['lname1']) && !isset($_POST['lname2'])) $action = "new";
                        include('newuser.php');
                        die();
                    }
                }
            }
        }
    }
}
?>

<div class='logo'>
    <img src="assets/img/BLFC-ChipIcon.png"/>
</div>
<div class='login'>
    <?php if (isset($message)) echo "<h2 class='error'>$message</h2>" ?>
    <h2>BLFC Volunteer Check-In</h2>
    <form name="login" method="post" action="">
        <input id='badgeID' name='badgeID' placeholder='Badge ID' value='<?php echo $badgeID ?>' type='text'/>
        <input id='lastName' name='lastName' placeholder='Last Name'
               value='<?php echo $lastName ?>'
            <?php if (empty($kiosk)) {
                echo "type='password'";
            } else {
                echo "type='text'";
            } ?>/>
        <!--
        <div class='remember'>
            <input checked='' id='remember' name='remember' type='checkbox'/>
            <label for='remember'></label>Remember me
        </div>
        -->
        <input type='submit' value='Continue'/>
    </form>
    <a class='badgehelp' href='#'>Badge ID?</a>
</div>

<div class="mask" role="dialog"></div>
<div class="modal" role="alert">
    <button class="close" role="button">X</button>
    <h2>Your badge ID is located on the bottom left of your badge.</h2>
    <img src="assets/img/BLFC-BadgeHelp.png"/>
</div>

<script>
    $(".badgehelp").on("click", function () {
        $(".mask").addClass("active");
    });

    // Function for close the Modal

    function closeModal() {
        $(".mask").removeClass("active");
    }

    // Call the closeModal function on the clicks/keyboard

    $(".close, .mask").on("click", function () {
        closeModal();
    });

    $(document).keyup(function (e) {
        if (e.keyCode == 27) {
            closeModal();
        }
    });
</script>