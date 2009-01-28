<strong><?= $group->name?></strong>
<? if (!$group->special): ?>
<a href="<?= url::site("users/delete_group_form/$group->id") ?>" class="gDialogLink"><?= t("delete") ?></a>
<? else: ?>
<span class="inactive" title="<?= t("This group cannot be deleted") ?>">
  <?= t("delete") ?>
</span>
<? endif ?>
<ul>
  <? foreach ($group->users as $i => $user): ?>
  <li class="gUser">
    <?= $user->name ?>
    <? if (!$group->special): ?>
    <a href="javascript:remove_user(<?= $user->id ?>, <?= $group->id ?>)">X</a>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
