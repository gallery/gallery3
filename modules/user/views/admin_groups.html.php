<? defined("SYSPATH") or die("No direct script access."); ?>
<div class="gBlock">
  <h2><?= _("Group Administration") ?></h2>
  <div class="gBlockContent">
    <p><?= _("These are the groups in your system") ?></p>
  </div>
  <ul>
    <? foreach ($groups as $i => $group): ?>
    <li>
      <?= $group->name ?>
      <a href="groups/edit_form/<?= $group->id ?>" class="gDialogLink"><?= _("edit") ?></a>
      <? if (!$group->special): ?>
        <a href="groups/delete_form/<?= $group->id ?>" class="gDialogLink"><?= _("delete") ?></a>
      <? endif ?>
    </li>
    <? endforeach ?>
    <li><a href="groups/add_form" class="gDialogLink"><?= _("Add group") ?></a></li>
  </ul>
</div>


