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
        <? $intent = access::group_intent($group, $permission->name, $item) ?>
        <? $allowed = access::group_can($group, $permission->name, $item) ?>
        <? $lock = access::locked_by($group, $permission->name, $item) ?>

        <? if ($lock): ?>

        <? if ($allowed): ?>
        allowed
        <? else: ?>
        denied
        <? endif ?>

        <a href="javascript:show(<?= $lock->id ?>)">(parental lock)</a>
        <? else: ?>

        <? if ($allowed): ?>
        <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">allowed</a>
        <? else: ?>
        <a href="javascript:set('allow',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">denied</a>
        <? endif ?>

        <? if ($intent === null): ?>
        (from parent)
        <? else: ?>
        <? if ($item->id != 1): ?>
        <a href="javascript:set('reset',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">(reset to parent)</a>
        <? endif ?>
        <? endif ?>

        <? endif ?>
      </td>
      <? endforeach ?>
    </tr>
    <? endforeach ?>
  </table>
</form>
