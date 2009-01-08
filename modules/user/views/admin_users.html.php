<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="gBlock">
  <h2><?= t("User Administration") ?></h2>
  <div class="gBlockContent">
    <p><?= t("These are the users in your system") ?></p>
    <ul>
      <? foreach ($users as $i => $user): ?>
      <li>
        <?= $user->name ?>
        <?= ($user->last_login == 0) ? "" : "(" . date("M j, Y", $user->last_login) . ")" ?>
        <a href="users/edit_form/<?= $user->id ?>" class="gDialogLink"><?= t("edit") ?></a>
        <? if (!(user::active()->id == $user->id || user::guest()->id == $user->id)): ?>
        <a href="users/delete_form/<?= $user->id ?>" class="gDialogLink">
          <?= t("delete") ?></a>
        <? endif ?>
      </li>
      <? endforeach ?>
      <li><a href="users/add_form" class="gDialogLink" title="<?= t("Add user") ?>">
        <?= t("Add user") ?></a></li>
    </ul>
  </div>
</div>
