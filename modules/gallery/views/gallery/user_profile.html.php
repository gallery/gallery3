<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    $("#g-profile-return").click(function(event) {
      history.go(-1);
      return false;
    })
  });
</script>
<div id="g-user-profile">
  <div class="ui-helper-clearfix">
    <a id="g-profile-return" class="g-button g-right ui-state-default ui-corner-all" href="#">
      <?= t("Return") ?>
    </a>
    <? if ($editable): ?>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link" href="<?= url::site("users/form_change_email/{$user->id}") ?>">
      <?= t("Change email") ?>
    </a>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link" href="<?= url::site("users/form_change_password/{$user->id}") ?>">
      <?= t("Change password") ?>
    </a>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link" href="<?= url::site("form/edit/users/{$user->id}") ?>">
      <?= t("Edit") ?>
    </a>
    <? endif ?>
    <? if ($contactable): ?>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link"
       href="<?= url::site("user_profile/contact/{$user->id}") ?>">
      <?= t("Contact") ?>
    </a>
    <? endif ?>
  </div>
  <h1>
    <img src="<?= $user->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
       alt="<?= html::clean_attribute($user->display_name()) ?>"
       class="g-avatar g-left" width="40" height="40" />
    <?= t("User profile: %name", array("name" => $user->display_name())) ?>
  </h1>
  <? foreach ($info_parts as $info): ?>
  <div class="g-block">
    <h2><?= html::purify($info->title) ?></h2>
    <div class="g-block-content">
    <?= $info->view ?>
    </div>
  </div>
  <? endforeach ?>
</div>
