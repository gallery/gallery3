<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
  #g-user-profile div {
    margin-top: 1em;
  }

  #g-user-profile fieldset {
    border: 1px solid #CCCCCC;
    padding: 0 1em 0.8em;
  }

  #g-user-profile fieldset label {
    font-weight: bold;
  }

  #g-user-profile fieldset div {
    padding-left: 1em;
  }

  #g-user-profile td {
    border: none;
    padding: 0;
  }
</style>
<script>
  $(document).ready(function() {
    $("#g-profile-return").click(function(event) {
      history.go(-1);
      return false;
    })
  });
</script>
<div id="g-user-profile">
  <h1>
    <a href="#">
      <img src="<?= $user->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
           alt="<?= html::clean_attribute($user->display_name()) ?>"
           class="g-avatar" width="40" height="40" />
    </a>
    <?= t("%name Profile", array("name" => $user->display_name())) ?>
  </h1>
  <? foreach ($info_parts as $info): ?>
  <div>
    <fieldset>
    <label><?= html::purify($info->title) ?></label>
    <div>
    <?= $info->view ?>
    </div>
    </fieldset>
  </div>
  <? endforeach ?>
  <div id="g-profile-buttons" class="ui-helper-clearfix g-right">
    <? if (!$user->guest && $not_current && !empty($user->email)): ?>
    <a class="g-button ui-icon-right ui-state-default ui-corner-all g-dialog-link"
       href="<?= url::site("user_profile/contact/{$user->id}") ?>">
      <?= t("Contact") ?>
    </a>
    <? endif ?>
    <? if ($editable): ?>
    <a class="g-button ui-icon-right ui-state-default ui-corner-all g-dialog-link" href="<?= url::site("form/edit/users/{$user->id}") ?>">
      <?= t("Edit") ?>
    </a>
    <a class="g-button ui-icon-right ui-state-default ui-corner-all g-dialog-link" href="<?= url::site("users/form_change_password/{$user->id}") ?>">
      <?= t("Change password") ?>
    </a>
    <a class="g-button ui-icon-right ui-state-default ui-corner-all g-dialog-link" href="<?= url::site("users/form_change_email/{$user->id}") ?>">
      <?= t("Change email") ?>
    </a>
    <? endif ?>

    <a id="g-profile-return" class="g-button ui-icon-right ui-state-default ui-corner-all" href="#">
      <?= t("Return") ?>
    </a>
  </div>
</div>
