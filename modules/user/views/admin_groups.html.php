<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="gBlock">
  <h2><?= _("Group Administration") ?></h2>
  <div class="gBlockContent">
    <p><?= _("These are the groups in your system") ?></p>
  </div>
  <ul>
    <? foreach ($groups as $i => $group): ?>
    <li>
      <?= $group->name ?>
      <a href="groups/edit_form/<?= $group->id ?>" class="gDialogLink"
        title="<?= _("Edit group") ?>"><?= _("edit") ?></a>
      <? if (!$group->special): ?>
        <a href="groups/delete_form/<?= $group->id ?>" class="gDialogLink"
          title="<?= sprintf(_("Do you really want to delete %s"), $group->name) ?>">
        <?= _("delete") ?></a>
      <? endif ?>
    </li>
    <? endforeach ?>
    <li><a href="groups/add_form" class="gDialogLink"
      title="<?= _("Add group") ?>"><?= _("Add group") ?></a></li>
  </ul>
</div>


