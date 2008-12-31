<?php defined("SYSPATH") or die("No direct script access.") ?>
<form method="post" action="<?= url::site("permissions/edit/$item->id") ?>">
  <?= access::csrf_form_field() ?>

  <table border=1>
    <tr>
      <th> </th>
      <? foreach ($groups as $group): ?>
      <th> <?= $group->name ?> </th>
      <? endforeach ?>
    </tr>

    <? foreach ($permissions as $permission): ?>
    <tr>
      <td> <?= _($permission->display_name) ?> </td>
      <? foreach ($groups as $group): ?>
      <td>
        <? $locked = access::locking_items($group, $permission->name, $item) ?>
        <? $allowed = access::group_can($group, $permission->name, $item) ?>
        <? if ($locked && $allowed): ?>
        allowed <a href="#">locked</a>
        <? elseif ($locked && !$allowed): ?>
        denied <a href="#">locked</a>
        <? elseif ($allowed): ?>
        <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">
          <?= _("allowed") ?>
        </a>
        <? elseif (!$allowed): ?>
        <a href="javascript:set('allow',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">
          <?= _("denied") ?>
        </a>
        <? endif ?>
      </td>
      <? endforeach ?>
    </tr>
    <? endforeach ?>
  </table>
</form>
