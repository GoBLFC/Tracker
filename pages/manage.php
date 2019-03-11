<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/20/2019
 * Time: 8:43 PM
 */

if (!defined('TRACKER')) die('No.');
if (!isManager($badgeID) && !isAdmin($badgeID)) die('Unauthorized.');
?>

<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header highvis">
            <div class="vistext">Management</div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="card-body">
                    <div class="card">
                        <div class="card-header cadHeader">
                            <div>Volunteer Management</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <div class="input-group">
                                        <input id="searchinput" type="text" class="form-control inputDark"
                                               placeholder="Badge Number, Name, Username..."
                                               aria-label="Search" aria-describedby="basic-addon2">
                                    </div>
                                    <table id="utable" class="table table table-striped table">
                                        <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Username</th>
                                            <th scope="col">Name</th>
                                            <th scope="col"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="uRow">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="display: none">
                        <div class="card-header cadHeader">
                            <div id="uHeadName" style="text-align: center;font-weight: bold;">User</div>
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
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <table id="table" class="table table table-striped table">
                                        <thead>
                                        <tr>
                                            <th scope="col">In</th>
                                            <th scope="col">Out</th>
                                            <th scope="col">Dept</th>
                                            <th scope="col">Worked</th>
                                            <th scope="col">Earned</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody id="eRow">
                                        </tbody>
                                    </table>

                                    <div class="input-group" style="display: flex">
                                        <div class="input-group date" id="timestart"
                                             data-target-input="nearest" style="width: auto;max-width: 50%;">
                                            <input type="text" class="form-control datetimepicker-input"
                                                   placeholder="Start" data-target="#timestart"/>
                                            <div class="input-group-append" data-target="#timestart"
                                                 data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group date" id="timestop"
                                             data-target-input="nearest" style="width: auto;max-width: 50%;">
                                            <input type="text" class="form-control datetimepicker-input"
                                                   placeholder="Stop" data-target="#timestop"/>
                                            <div class="input-group-append" data-target="#timestop"
                                                 data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <select style="width: 10%" class="selectpicker"
                                                title="Department" data-live-search="true" id="dept">
                                        </select>
                                        <input type="text" class="form-control inputDark" placeholder="Notes"
                                               aria-label="Notes" aria-describedby="basic-addon2"
                                               style="width: 10%;" id="notes">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    style="background-color: #004014; color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.15)"
                                                    onClick="ad dTime()">
                                                Add Time
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header cadHeader">
                            <div>Recent Check-In / Check-Out</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card novis">
            <div class="row">
                <div class="col-sm">
                    <a href="/tracker/" style="float:right"
                       role="button" class="btn btn-sm btn-info">BACK
                    </a></div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="top: 5em;position: relative;">
    <div class="card novis">
        <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...</div>
    </div>
</div>

<script src="js/landing.js"></script>
<script src="js/general.js"></script>
<script src="js/manager.js"></script>

<script>$(document).ready(function () {
        initData();
        decrementLogout();
    });
</script>