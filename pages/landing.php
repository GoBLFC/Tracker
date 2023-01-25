<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/21/2018
 * Time: 4:44 PM
 */

if (!defined('TRACKER')) die('No.');

// Load department list
$departments = getDepartments(false);
$cDept = getCheckIn($badgeID);
if ($cDept) $cDept = $cDept[0];
?>
    <div class="container" style="top: 5em;position: relative;">
        <div class="card">
            <div class="card-header">
                <?php //echo 'Check-' . ($cDept ? "Out" : "In") ?>
                <?php echo "Welcome " . $user[0]['nickname'] . "!" ?>
            </div>

            <div class="container">
                <div class="row">
                    <div class="col-sm">
                        <div class="card-body">
                            <div id="checkstatus" class="alert alert-<?php echo($cDept ? "success" : "danger") ?>"
                                 style="padding: 0.4rem 1rem; margin: 0>" role="alert">
                                You are currently <?php echo($cDept ? "" : "not") ?> checked in.
                            </div>

                            <!--<p class="card-text">Select a department from the list on the right.</p>-->
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="card-body">
                            <select <?php echo($cDept ? "disabled " : "") ?> id="dept"
                                                                             class="custom-select custom-select-lg mb-3"
                                                                             style="margin-bottom: 0 !important;">

                                <?php if (!$cDept) { ?>
                                    <option value="-1" disabled selected hidden>Select Department</option>
                                <?php } ?>
                                <?php foreach ($departments as $dept) echo "<option " . (($cDept && $cDept['dept'] == $dept['id']) ? "selected" : "") . " value='" . $dept['id'] . "'>" . $dept['name'] . "</option>"; ?>

                            </select>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="card-body">
                            <a href="#" id="checkinout" class="btn btn-block btn-primary"
                               data-value="<?php echo($cDept ? "out" : "in") ?>"><?php echo 'Check-' . ($cDept ? "Out" : "In") ?></a>
                        </div>
                    </div>
                </div>
                <?php
                include('pages/adminFunctions.php');
                ?>
            </div>
        </div>
    </div>

    <div class="container" style="top: 5em;position: relative;">
        <div class="card">
            <div class="card-header">
                Your Stats
            </div>

            <div class="row">
                <div class="col-sm" id="currdurr" style="display: none;padding: 0.3em;">
                    <div class="card-body">
                        <div class="statistic">
                            <div class="value"><img src="assets/img/clock-circular-outline.png"
                                                    class="img-circle inline image"> <span
                                        id="durrval" style="text-transform: none">Loading</span>
                            </div>
                            <div class="label">Shift Duration</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm" style="padding: 0.3em;">
                    <div class="card-body">
                        <div class="statistic">
                            <div class="value"><img src="assets/img/clock-circular-outline.png"
                                                    class="img-circle inline image"> <span
                                        id="hourstoday" style="text-transform: none;">Loading</span>
                            </div>
                            <div class="label">Hours Today</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm" style="padding: 0.3em;">
                    <div class="card-body">
                        <div class="statistic">
                            <div class="value"><img src="assets/img/clock-circular-outline.png"
                                                    class="img-circle inline image"> <span
                                        id="earnedtime" style="text-transform: none">Loading</span>
                            </div>
                            <div class="label">Hours Earned</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <audio id='audio' preload="auto" src="/assets/egg/hero.ogg"></audio>
    </div>

    <div class="container" style="top: 5em;position: relative;">
        <div class="card novis">
            <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...
                <a role="button" class="btn btn-light btn-sm" href="/?logout" style="">Logout Now</a>
            </div>
        </div>
    </div>

    <div class="container" style="top: 5em;position: relative;">
        <div class="card novis">
            <div class="telegramBox"><img src="/assets/img/icons/telegram.png">
                <a role="button" class="btn btn-light btn-md telegramButton" data-toggle="modal"
                   data-target="#exampleModalCenter">Telegram Bot</a>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle" style="color: black;">Scan to add bot</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <img src="https://chart.googleapis.com/chart?chs=500x500&amp;cht=qr&amp;chl=<?php echo urlencode("https://t.me/BLFC_BOT?start=" . getTGUID($badgeID)) ?>&amp;choe=UTF-8"
                     title="Opens Telegram to add bot." style="width: 100%;">
                <ul class="list-unstyled" style="color:black; margin-left: 30px;">
                    <li>This bot can provide you:</li>
                    <ul>
                        <li>Hours Clocked</li>
                        <li>Eligible Rewards</li>
                        <li>Quick Login Code</li>
                    </ul>
                    </li>
                    <li>This bot will also:</li>
                    <ul>
                        <li>Remind you when you can get a reward</li>
                    </ul>
                    </li>
                </ul>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/landing.js?v=3"></script>
    <script>$(document).ready(function () {
            initData();
            clockCycle();
            decrementLogout();
        });
    </script>

<?php //echo calculateBonusTime(1234);
?>