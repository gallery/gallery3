<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
  #g-user-profile #g-profile-buttons {
    bottom: 0;
    position: absolute;
    right: 0;
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
  $("#g-user-profile").ready(function() {
                                           //$("#g-profile-return").click(function(event) {
    //  window.location = <?= $return->for_js() ?>;
                                           //});
  });
</script>
<div id="g-user-profile" style="height: <?= $height ?>px">
  <h1 style="display: none"><?= t("%name Profile", array("name" => $user->display_name())) ?></h1>
  <div>
    <fieldset>
    <label><?= t("User information") ?></label>
    <div>
    <table>
    <? foreach ($fields as $field => $value): ?>
    <tr>
      <td><?= $field ?></td>
      <td><?= $value ?></td>
    </tr>
    <? endforeach ?>
    </table>
    </div>
    </fieldset>
  </div>
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
    <? endif ?>

    <a class="g-button ui-icon-right ui-state-default ui-corner-all" href="<?= $return->for_html_attr() ?>">
      <?= t("Return") ?>
    </a>
  </div>
</div>