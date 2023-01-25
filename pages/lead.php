<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/20/2019
 * Time: 8:43 PM
 */

if (!defined('TRACKER')) die('No.');
if (!isLead($badgeID)) die('Unauthorized.');
?>

<div class="container" style="top: 5em;position: relative;">
    <div class="card">
        <div class="card-header highvis">
            <div class="vistext">Lead</div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="card-body">
					
                    <div class="card">
                        <div class="card-header cadHeader">
                            <div>Kiosk Settings</div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card-header cadBody">
									<center>
										<button data-status=<?php echo $kioskAuth ?> onclick="toggleKiosk(this)"
												type="button"
												class="btn btn-sm btn-<?php echo($kioskAuth == 1 ? "danger" : "warning") ?>
										"><?php echo($kioskAuth == 1 ? "Deauthorize" : "Authorize") ?> Kiosk
										</button>
									</center>
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

<script src="js/landing.js"></script>
<script src="js/general.js"></script>
<script src="js/manager.js"></script>

<script>$(document).ready(function () {
        initData();
        decrementLogout();
    });
</script>