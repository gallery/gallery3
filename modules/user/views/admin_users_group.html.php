<?php defined("SYSPATH") or die("No direct script access.") ?>
<strong><?= $group->name ?></strong>
<? if (!$group->special): ?>
<a href="<?= url::site("admin/users/delete_group_form/$group->id") ?>"
  title="<?= t("Delete " . $group->name) ?>"
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
    <?= $user->name ?>
    <? if (!$group->special): ?>
    <a href="javascript:remove_user(<?= $user->id ?>, <?= $group->id ?>)"
       class="gButtonLink ui-state-default ui-corner-all ui-icon-left">
      <span class="ui-icon ui-icon-closethick">Remove <?= $user->name ?> from <?= $group->name ?></span></a>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
