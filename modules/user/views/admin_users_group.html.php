<?php defined("SYSPATH") or die("No direct script access.") ?>
<h4>
  <?= html::clean($group->name) ?>
  <? if (!$group->special): ?>
  <a href="<?= url::site("admin/users/delete_group_form/$group->id") ?>"
    title="<?= t("Delete the %name group", array("name" => $group->name))->for_html_attr() ?>"
    class="g-dialog-link g-button ui-state-default ui-corner-all">
    <span class="ui-icon ui-icon-trash"><?= t("Delete") ?></span></a>
  <? else: ?>
  <a title="<?= t("This default group cannot be deleted")->for_html_attr() ?>"
     class="g-dialog-link g-button ui-state-disabled ui-corner-all ui-icon-left">
    <span class="ui-icon ui-icon-trash"><?= t("Delete") ?></span></a>
  <? endif ?>
</h4>

<? if ($group->users->count() > 0): ?>
<ul>
  <? foreach ($group->users as $i => $user): ?>
  <li class="g-user">
    <?= html::clean($user->name) ?>
    <? if (!$group->special): ?>
    <a href="javascript:remove_user(<?= $user->id ?>, <?= $group->id ?>)"
       class="g-button ui-state-default ui-corner-all ui-icon-left"
       title="<?= t("Remove %user from %group group",
              array("user" => $user->name, "group" => $group->name))->for_html_attr() ?>">
      <span class="ui-icon ui-icon-closethick"><?= t("Remove") ?></span>
    </a>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
<? else: ?>
<div>
  <p>
    <?= t("Drag &amp; drop users from the \"User admin\" above into this group box to add group members.") ?>
  </p>
</div>
<? endif ?>
