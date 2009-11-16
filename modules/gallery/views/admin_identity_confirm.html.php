<?php defined("SYSPATH") or die("No direct script access.") ?>
<form method="post" action="<?= url::site("admin/identity/change") ?>">
  <?= access::csrf_form_field() ?>
  <?= form::hidden("provider", $new_provider) ?>

  <p><span class="ui-icon ui-icon-alert" style="float: left; margin:0 7px 20px 0;"></span>
  <?= t("Are you sure you want to change your Identity Provider? Continuing will delete all existing users.") ?>
  </p>
</form>

