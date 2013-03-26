<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-users-delete-user">
  <p>
    <?= t("Really delete <b>%name</b>?  Any photos, movies or albums owned by this user will transfer ownership to <b>%new_owner</b>.", array("name" => $user->display_name(), "new_owner" => identity::active_user()->display_name())) ?>
  </p>
  <?= $form ?>
</div>
