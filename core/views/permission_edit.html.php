<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gPermissions">
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
          <? $locks = access::locking_items($group, $permission->name, $item) ?>
          <input type="checkbox"
                 name="<?= "{$permission->name}_$group->id" ?>"
                 value="1"
                 <? if (access::group_can($group, $permission->name, $item)): ?> checked="checked" <? endif ?>
            <? if ($locks): ?> disabled="disabled" <? endif ?>
            />
          <? if ($locks): ?>
          Locked by: <!-- Not internationalized because its hard and this is prob. the wrong UI anyway -->
          <? foreach ($locks as $lock): ?>
          <a href="<?= url::site("{$lock->type}s/$lock->id") ?>"><?= $lock->title ?></a>
          <? endforeach ?>
          <? endif ?>
        </td>
        <? endforeach ?>
      </tr>
      <? endforeach ?>
    </table>
    <input type="submit" value="<?= _("Save") ?>"/>
  </form>
</div>
