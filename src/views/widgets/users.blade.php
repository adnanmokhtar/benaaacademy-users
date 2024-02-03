<?php if(count($users)){ ?>

<div class="row">
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>
                    <i class="fa fa-users"></i> &nbsp;
                    <?php echo trans("users::users.recent_users") ?>
                </h5>

                <strong class="pull-right">
                    <a class="text-navy" href="<?php echo route("admin.users.show"); ?>">
                        <?php echo trans("users::users.show_all"); ?>
                    </a>
                </strong>
            </div>
            <div class="ibox-content">
                <table class="table table-striped table-hover">

                    <tbody class="valign-middle">

                    <?php foreach ($users as $user) { ?>
                    <tr>
                        <td>
                            <?php if ($user->photo != "") { ?>
                            <img src="<?php echo thumbnail($user->photo->path); ?>" alt=""
                                 style="width:26px;height:26px;" class="rounded">
                            <?php } else { ?>
                            <img src="<?php echo assets("admin::images/user.png"); ?>" alt=""
                                 style="width:26px;height:26px;" class="rounded">
                            <?php } ?>
                        </td>

                        <td>
                            <a class="text-navy"
                               href="<?php echo route("admin.users.edit", array("id" => $user->id)); ?>">
                                <?php echo $user->first_name . " " . $user->last_name; ?>
                            </a>
                        </td>

                        <td class="text-right">
                            <small>{{ $user->role ? $user->role->name : trans("users::users.no_role") }}</small>
                        </td>
                    </tr>

                    <?php } ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php } ?>
