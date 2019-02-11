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
                    <div class="card">
                        <div class="card-header cadHeader">
                            <div>Site Settings</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
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
                    <div class="card">
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
                                                <th scope="col">Properties</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr id="p22">
                                                <th scope="row">1234</th>
                                                <td>Billy Bob</td>
                                                <td>
                                                    <span class="badge badge-pill badge-danger">Tag 1</span>
                                                    <span class="badge badge-pill badge-info">123</span>
                                                    <span class="badge badge-pill badge-info">456</span>
                                                </td>
                                                <td>
                                                    <button id="22" type="button" class="btn btn-sm btn-danger">Remove
                                                    </button>
                                                </td>
                                            </tr>
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
                                                <th scope="col">Properties</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr id="p22">
                                                <th scope="row">1234</th>
                                                <td>Billy Bob</td>
                                                <td>
                                                    <span class="badge badge-pill badge-danger">Tag 1</span>
                                                    <span class="badge badge-pill badge-info">123</span>
                                                    <span class="badge badge-pill badge-info">456</span>
                                                </td>
                                                <td>
                                                    <button id="22" type="button" class="btn btn-sm btn-danger">Remove
                                                    </button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card">
                                        <div class="input-group">
                                            <input type="text" class="form-control inputDark" placeholder="Badge Number"
                                                   aria-label="Recipient's username" aria-describedby="basic-addon2">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button"
                                                        style="background-color: #400000; color: #ffffff">Make Admin
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button"
                                                        style="background-color: #402300; color: #ffffff">Make Manager
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
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
                                            <th scope="col">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr id="p22">
                                            <th scope="row">99</th>
                                            <td>Other/Unknown</td>
                                            <td>No</td>
                                            <td>
                                                <button id="22" type="button" class="btn btn-sm btn-danger">Remove
                                                </button>
                                                <button id="22" type="button" class="btn btn-sm btn-warning">Rename
                                                </button>
                                            </td>
                                        </tr>
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
                                                    style="background-color: #004014; color: #ffffff">Add Dept
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
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
                                            <th scope="col">Departments</th>
                                            <th scope="col">Modifier</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr id="p22">
                                            <td>2019-02-03 18:00:00</td>
                                            <td>2019-02-03 18:00:00</td>
                                            <td>1, 2, 3</td>
                                            <td>2x</td>
                                            <td>
                                                <button id="22" type="button" class="btn btn-sm btn-danger">Remove
                                                </button>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="input-group">
                                        <input type="text" class="form-control inputDark" placeholder="Start"
                                               aria-label="Recipient's username" aria-describedby="basic-addon2"
                                               style="width: 25%;">
                                        <input type="text" class="form-control inputDark" placeholder="Stop"
                                               aria-label="Recipient's username" aria-describedby="basic-addon2"
                                               style="width: 25%;">
                                        <select style="width: 25%" class="selectpicker"
                                                title="Departments" multiple
                                                data-live-search="true">
                                            <option>Dept 1</option>
                                            <option>Dept 2</option>
                                            <option>Dept 3</option>
                                        </select>
                                        <input type="text" class="form-control inputDark" placeholder="Modifier"
                                               aria-label="Recipient's username" aria-describedby="basic-addon2"
                                               style="width: 15%;">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    style="background-color: #004014; color: #ffffff;">Add Bonus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header cadHeader">
                            Generate Reports
                        </div>
                        <div class="card-body cadBody">
                            <div class="row">
                                <div class="col-sm">
                                    <a role="button" class="btn btn-dark" href="#" style="display:grid">Unclocked
                                        Users
                                    </a>
                                </div>
                                <div class="col-sm">
                                    <div class="dropdown">
                                        <button style="width:100%"
                                                class="btn btn-dark btn-secondary dropdown-toggle"
                                                type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                            Volunteer Hours
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" href="#">> 1 Hour</a>
                                            <a class="dropdown-item" href="#">> 4 Hours</a>
                                            <a class="dropdown-item" href="#">> 8 Hours</a>
                                            <a class="dropdown-item" href="#">> 12 Hours</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="input-group">
                                        <input type="text" class="form-control inputDark" placeholder="Badge #"
                                               aria-label="Recipient's username" aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    style="background-color: #000840; color: #ffffff">Volunteer
                                            </button>
                                        </div>
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
<script src="js/admin.js"></script>
<script>$(document).ready(function () {
        decrementLogout();
    });
</script>