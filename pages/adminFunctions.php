<?php
if (isManager($badgeID) || isAdmin($badgeID)) {
    ?>
    <div class="card">
        <div class="card-header">
            Your Functions
        </div>
        <div class="row">
            <?php
            if (isManager($badgeID) || isAdmin($badgeID)) {
                ?>
                <div class="col-sm">
                    <a role="button" class="btn btn-light" href="manage" style="width:100%">Management
                        Panel
                    </a>
                </div>
                <?php
            }
            if (isAdmin($badgeID)) {
                ?>
                <div class="col-sm">
                    <a role="button" class="btn btn-light" href="admin" style="width:100%">Admin
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