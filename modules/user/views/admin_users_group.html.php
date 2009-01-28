<strong><?= $group->name?></strong>
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
