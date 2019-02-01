<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 10/21/2018
 * Time: 4:44 PM
 */

if (!defined('TRACKER')) die('No.');

// Load department list
$departments = getDepartments(0);
?>
<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header">
            Check-In / Check-Out
        </div>

        <div class="container">
            <div class="row">
                <div class="col-sm">
                    <div class="card-body">
                        <div class="alert alert-danger" style="padding: 0.4rem 1rem; margin: 0;" role="alert">Not
                            currently checked
                            in.
                        </div>
                        <!--<p class="card-text">Select a department from the list on the right.</p>-->
                    </div>
                </div>
                <div class="col-sm">
                    <div class="card-body">
                        <select id="dept" class="custom-select custom-select-lg mb-3" style="margin-bottom: 0 !important;">
                            <option value="-1" disabled selected hidden>Select Department </option>
                            <?php foreach ($departments as $dept) echo "<option value='" . $dept['id'] . "'>" . $dept['name'] . "</option>"; ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="card-body">
                        <a href="#" id="checkin" class="btn btn-block btn-primary">Check-In</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header">
            Your Stats
        </div>

        <div class="row">
            <div class="col-sm">
                <div class="card-body">
                    <div class="statistic">
                        <div class="value"><img src="/tracker/assets/img/clock-circular-outline.png"
                                                class="img-circle inline image"> 0
                        </div>
                        <div class="label">Hours Today</div>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="card-body">
                    <div class="statistic">
                        <div class="value"><img src="/tracker/assets/img/clock-circular-outline.png"
                                                class="img-circle inline image"> 0
                        </div>
                        <div class="label">Hours Earned</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...</div>
    </div>
</div>
<script src="js/landing.js"></script>