<? defined("SYSPATH") or die("No direct script access."); ?>
<div class="gBlock">
  <h2>Group Administration</h2>
  <div class="gBlockContent">
    <p>These are the groups in your system</p>
  </div>
  <ul>
    <? foreach ($groups as $i => $group): ?>
    <li>
      <?= $group->name ?>
      <a href="groups/edit_form/<?= $group->id ?>" class="gDialogLink">edit</a>
      <? if (!$group->special): ?>
        <a href="groups/delete_form/<?= $group->id ?>" class="gDialogLink">delete</a>
      <? endif ?>
    </li>
    <? endforeach ?>
    <li><a href="groups/create_form" class="gDialogLink">Add group</a></li>
  </ul>
</div>


