<?php
if (isManager($badgeID) || isAdmin($badgeID) || isLead($badgeID)) {
    ?>
    <div class="card">
        <div class="card-header">
            Your Functions
        </div>
        <div class="row">
            <?php
			if (isLead($badgeID)) {
                ?>
                <div class="col-sm">
                    <a role="button" class="btn btn-light" href="lead" style="width:100%;margin-bottom: 5px">Lead
                        Panel
                    </a>
                </div>
                <?php
            }
            if (isManager($badgeID) || isAdmin($badgeID)) {
                ?>
                <div class="col-sm">
                    <a role="button" class="btn btn-light" href="manage" style="width:100%;margin-bottom: 5px">Management
                        Panel
                    </a>
                </div>
                <?php
            }
            if (isAdmin($badgeID)) {
                ?>
                <div class="col-sm">
                    <a role="button" class="btn btn-light" href="admin" style="width:100%;margin-bottom: 5px">Admin
                        Panel
                    </a>
                </div>
                <?php
            } ?>
        </div>
    </div>
    <?php
}
?>