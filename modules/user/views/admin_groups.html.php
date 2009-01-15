<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="gBlock">
  <h2><?= t("Group Administration") ?></h2>
  <div class="gBlockContent">
    <p><?= t("These are the groups in your system") ?></p>
  </div>
  <ul>
    <? foreach ($groups as $i => $group): ?>
    <li>
      <?= $group->name ?>
      <a href="groups/edit_form/<?= $group->id ?>" class="gDialogLink"
        title="<?= t("Edit group") ?>"><?= t("edit") ?></a>
      <? if (!$group->special): ?>
        <a href="groups/delete_form/<?= $group->id ?>" class="gDialogLink"
          title="<?= t("Do you really want to delete %group_name", array("group_name" => $group->name)) ?>">
        <?= t("delete") ?></a>
      <? endif ?>
    </li>
    <? endforeach ?>
    <li><a href="groups/add_form" class="gDialogLink"
      title="<?= t("Add group") ?>"><?= t("Add group") ?></a></li>
  </ul>
</div>


