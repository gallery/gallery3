<? defined("SYSPATH") or die("No direct script access."); ?>
<table class="gMetadata">
  <tbody>
    <tr>
      <th><?= _("Title:") ?></th>
      <td><?= $item->title_edit; ?></td>
    </tr>
    <tr>
      <th><?= _("Description:") ?></th>
      <td><?= $item->description_edit; ?></td>
    </tr>
    <tr>
      <th><?= _("Name:") ?></th>
      <td><?= $item->name_edit; ?></td>
    </tr>
    <? if ($item->owner): ?>
    <tr>
      <th><?= _("Owner:") ?></th>
      <td><a href="#"><?= $item->owner->name ?></a></td>
    </tr>
    <? endif ?>
    <tr>
      <td colspan="2" class="toggle">
        <a href="#">more \/</a>
      </td>
    </tr>
  </tbody>
</table>


