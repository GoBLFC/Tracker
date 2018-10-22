<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/21/2018
 * Time: 6:16 PM
 */

unset($message);
if (!isset($action)) {
    if (!isset($_POST['lname1']) || !isset($_POST['lname2'])) {
        $message = "Please enter your last name in both fields.";
    } else {
        if ($_POST['lname1'] == "" || $_POST['lname2'] == "") {
            $message = "Last name cannot be blank!";
        } else if ($_POST['lname1'] == $_POST['lname2']) {
            $message = "VALIDATED. CREATE USER AND GO TO LANDING PAGE.";
            $lastName = $_POST['lname1'];

            createUser($badgeID, $lastName, $session);

            $action = "new";
            include('landing.php');
            die();
        } else {
            $message = "Names do not match!";
        }
    }
}
?>

<div class='logo'>
    <img src="assets/img/BLFC-ChipIcon.png"/>
</div>
<div class='login'>
    <?php if (isset($message)) echo "<h2 class='error'>$message</h2>" ?>
    <h2>New Volunteer - Name Confirmation</h2>
    <h1 style="font-size: 1em;">Please make sure this is right, else you won't be able to sign in again!</h1>
    <form name="login" method="post" action="">
        <input id='badgeID' name='badgeID' value='<?php echo $badgeID ?>' type='hidden'/>
        <input id='lastName' name='lastName' value='<?php echo $lastName ?>' type='hidden'/>
        <input id='lname1' name='lname1' placeholder='Last Name'
               value='<?php if (isset($_POST['lname1'])) echo $_POST['lname1'] ?>' type='text'/>
        <input id='lname2' name='lname2' placeholder='Re-enter Last Name' type='text'/>
        <input type='submit' value='Continue'/>
    </form>
</div>