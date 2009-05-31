<?php defined("SYSPATH") or die("No direct script access.") ?>
<strong><?= p::clean($group->name) ?></strong>
<? if (!$group->special): ?>
<a href="<?= url::site("admin/users/delete_group_form/$group->id") ?>"
  title="<?= t("Delete %name", array("name" => p::clean($group->name))) ?>"
  class="gDialogLink gButtonLink ui-state-default ui-corner-all">
  <span class="ui-icon ui-icon-trash"><?= t("delete") ?></span></a>
<? else: ?>
<a title="<?= t("This group cannot be deleted") ?>"
   class="gDialogLink gButtonLink ui-state-disabled ui-corner-all ui-icon-left">
  <span class="ui-icon ui-icon-trash"><?= t("delete") ?></span></a>
<? endif ?>
<ul>
  <? foreach ($group->users as $i => $user): ?>
  <li class="gUser">
    <?= p::clean($user->name) ?>
    <? if (!$group->special): ?>
    <a href="javascript:remove_user(<?= $user->id ?>, <?= $group->id ?>)"
       class="gButtonLink ui-state-default ui-corner-all ui-icon-left">
      <span class="ui-icon ui-icon-closethick">
        <?= t("Remove %user from %group",
              array("user" => p::clean($user->name), "group" => p::clean($group->name))) ?>
      </span>
    </a>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
