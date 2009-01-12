<?php defined("SYSPATH") or die("No direct script access.") ?>
<form method="post" action="<?= url::site("permissions/edit/$item->id") ?>">
  <?= access::csrf_form_field() ?>
  <fieldset>
    <legend> <?= t("Edit Permissions") ?> </legend>

    <table>
      <tr>
        <th> </th>
        <? foreach ($groups as $group): ?>
        <th> <?= $group->name ?> </th>
        <? endforeach ?>
      </tr>

      <? foreach ($permissions as $permission): ?>
      <tr>
        <td> <?= t($permission->display_name) ?> </td>
        <? foreach ($groups as $group): ?>
        <td>
          <? $intent = access::group_intent($group, $permission->name, $item) ?>
          <? $allowed = access::group_can($group, $permission->name, $item) ?>
          <? $lock = access::locked_by($group, $permission->name, $item) ?>

          <? if ($lock): ?>
            <?= t("denied and locked by") ?> <a href="javascript:show(<?= $lock->id ?>)"><?= t("parent") ?></a>
          <? else: ?>
            <? if ($intent === null): ?>
              <? if ($allowed): ?>
                <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">allowed by parent</a>
              <? else: ?>
                <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">denied by parent</a>
              <? endif ?>
            <? elseif ($intent === access::DENY): ?>
              <a href="javascript:set('allow',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">denied</a>
            <? elseif ($intent === access::ALLOW): ?>
              <? if ($item->id == 1): ?>
              <a href="javascript:set('deny',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">allowed</a>
              <? else: ?>
              <a href="javascript:set('reset',<?= $group->id ?>,<?= $permission->id ?>,<?= $item->id ?>)">allowed</a>
              <? endif ?>
            <? endif ?>
          <? endif ?>
        </td>
        <? endforeach ?>
      </tr>
      <? endforeach ?>
    </table>
  </fieldset>
</form>
