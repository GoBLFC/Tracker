<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/9/2019
 * Time: 10:56 PM
 */

if (!defined('TRACKER')) die('No.');
if (!isAdmin($badgeID)) die('Unauthorized.');
?>

<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header highvis">
            <div class="vistext">Admin</div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="card-body">
                    <?php include('navbar.php'); ?>

                    <div class="card" data-section="Site">
                        <div class="card-header cadHeader">
                            <div>Site Settings</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <button data-status=<?php echo $devMode . " " ?>
                                            onclick="toggleDevmode(this)"
                                            type="button"
                                            class="btn btn-sm btn-<?php echo($devMode == 1 ? "danger" : "warning") ?>
                    "><?php echo($devMode == 1 ? "Disable" : "Enable") ?> Dev Mode
                                    </button>
                                    <button data-status=<?php echo $siteStatus . " " ?>
                                            onclick="toggleSite(this)"
                                            type="button"
                                            class="btn btn-sm btn-<?php echo($siteStatus == 1 ? "danger" : "success") ?>
                    "><?php echo($siteStatus == 1 ? "Disable" : "Enable") ?> Site
                                    </button>
                                    <button data-status=<?php echo $kioskAuth ?> onclick="toggleKiosk(this)"
                                            type="button"
                                            class="btn btn-sm btn-<?php echo($kioskAuth == 1 ? "danger" : "warning") ?>
                    "><?php echo($kioskAuth == 1 ? "Deauthorize" : "Authorize") ?> Kiosk
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" data-section="Users" style="display:none;">
                        <div class="card-header cadHeader">
                            <div>User Settings</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <div class="card">
                                        <div class="card-header">
                                            Admins
                                        </div>
                                        <table id="table" class="table table table-striped table">
                                            <thead>
                                            <tr>
                                                <th scope="col">Badge</th>
                                                <th scope="col">Name</th>
                                                <!--<th scope="col">Properties</th>-->
                                                <th scope="col">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody id="uRowAdmin">
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            Managers
                                        </div>
                                        <table id="table" class="table table table-striped table">
                                            <thead>
                                            <tr>
                                                <th scope="col">Badge</th>
                                                <th scope="col">Name</th>
                                                <!--<th scope="col">Properties</th>-->
                                                <th scope="col">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody id="uRowManager">
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            Banned
                                        </div>
                                        <table id="table" class="table table table-striped table">
                                            <thead>
                                            <tr>
                                                <th scope="col">Badge</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody id="uRowBanned">
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card">
                                        <div class="input-group">
                                            <input id="" type="text" class="form-control inputDark"
                                                   placeholder="Badge Number"
                                                   aria-label="Badge Number" aria-describedby="basic-addon2">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button"
                                                        onClick="setAdmin(1, getButtonInput(this))"
                                                        style="background-color: #400000; color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.15)">
                                                    Make Admin
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button"
                                                        onClick="setManager(1, getButtonInput(this))"
                                                        style="background-color: #402300; color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.15)">
                                                    Make Manager
                                                </button>
                                                <button class="btn btn-outline-secondary highvis banButton"
                                                        type="button"
                                                        onClick="setBanned(1, getButtonInput(this))">
                                                    BAN
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" data-section="Departments" style="display:none;">
                        <div class="card-header cadHeader">
                            <div>Department Settings</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <table id="table" class="table table table-striped table">
                                        <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Department</th>
                                            <th scope="col">Hidden</th>
                                            <!--<th scope="col">Actions</th>-->
                                        </tr>
                                        </thead>
                                        <tbody id="dRow">
                                        </tbody>
                                    </table>
                                    <div class="input-group">
                                        <input type="text" class="form-control inputDark" placeholder="Name"
                                               aria-label="Name" aria-describedby="basic-addon2" style="width:55%">
                                        <select class="custom-select inputDark" id="inputGroupSelect02">
                                            <option value=-1 hidden selected>Hidden</option>
                                            <option value=0>No</option>
                                            <option value=1>Yes</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    style="background-color: #004014; color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.15)"
                                                    onclick="addDept(this)">
                                                Add Dept
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" data-section="Bonuses" style="display:none;">
                        <div class="card-header cadHeader">
                            <div>Bonus Settings</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
                                    <table id="table" class="table table table-striped table">
                                        <thead>
                                        <tr>
                                            <th scope="col">Start</th>
                                            <th scope="col">Stop</th>
                                            <th scope="col">Depts</th>
                                            <th scope="col">Mod</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody id="bRow">
                                        </tbody>
                                    </table>

                                    <div class="input-group" style="display: flex">
                                        <div class="input-group date" id="bonusstart"
                                             data-target-input="nearest" style="width: auto;max-width: 50%;">
                                            <input type="text" class="form-control datetimepicker-input"
                                                   placeholder="Start" data-target="#bonusstart"/>
                                            <div class="input-group-append" data-target="#bonusstart"
                                                 data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group date" id="bonusstop"
                                             data-target-input="nearest" style="width: auto;max-width: 50%;">
                                            <input type="text" class="form-control datetimepicker-input"
                                                   placeholder="Stop" data-target="#bonusstop"/>
                                            <div class="input-group-append" data-target="#bonusstop"
                                                 data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <select style="width: 10%" class="selectpicker dropup"
                                                title="Departments" multiple
                                                data-live-search="true" id="depts">
                                        </select>
                                        <input type="text" class="form-control inputDark" placeholder="Modifier"
                                               aria-label="Modifier" aria-describedby="basic-addon2"
                                               style="width: 10%;" id="bonusmod">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    style="background-color: #004014; color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.15)"
                                                    onClick="addBonus(this)">
                                                Add Bonus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" data-section="Reports" style="display:none;">
                        <div class="card-header cadHeader">
                            Generate Reports
                        </div>
                        <div class="card-body cadBody">
                            <div class="row">
                                <div class="col-sm" style="margin-bottom: 5px;">
                                    <a role="button" class="btn btn-dark" style="display:grid"
                                       onclick="changeFrame('unclocked')">Unclocked
                                        Users
                                    </a>
                                </div>
                                <div class="col-sm" style="margin-bottom: 5px;">
                                    <div class="dropdown">
                                        <button style="width:100%"
                                                class="btn btn-dark btn-secondary dropdown-toggle"
                                                type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                            Volunteer Hours
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" onclick="changeFrame('vHour1')">> 1 Hour</a>
                                            <a class="dropdown-item" onclick="changeFrame('vHour4')">> 4 Hours</a>
                                            <a class="dropdown-item" onclick="changeFrame('vHour8')">> 8 Hours</a>
                                            <a class="dropdown-item" onclick="changeFrame('vHour12')">> 12 Hours</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm" style="margin-bottom: 5px;">
                                    <div class="input-group">
                                        <input type="text" class="form-control inputDark" placeholder="Badge #"
                                               aria-label="Recipient's username" aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    style="background-color: #000840; color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.15)">
                                                Volunteer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm" style="margin-bottom: 5px;">
                                    <a role="button" class="btn btn-dark" style="display:grid"
                                       onclick="changeFrame('logs')">Staff Logs
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm" style="margin-bottom: 5px;">
                                    <a role="button" class="btn btn-dark" style="display:grid"
                                       onclick="changeFrame('apps')">Volunteer Applications
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm">
                                    <div class="card-header cadBody" style="padding: 10px 0 0 0;">
                                        <iframe id="datFrame" src=""
                                                style="width: 100%;height: 700px;border:none;display: none"></iframe>
                                    </div>
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
                    <a href="/" style="float:right"
                       role="button" class="btn btn-sm btn-info">BACK
                    </a></div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="top: 5em;position: relative;">
    <div class="card novis">
        <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...
            <a role="button" class="btn btn-light btn-sm" href="/?logout" style="">Logout Now</a>
        </div>
    </div>
</div>

<script src="js/landing.js?v=3"></script>
<script src="js/general.js?v=3"></script>
<script src="js/admin.js?v=3"></script>

<script>$(document).ready(function () {
        initData();
        decrementLogout();
    });
</script>