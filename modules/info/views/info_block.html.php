<?php defined("SYSPATH") or die("No direct script access.") ?>
<table class="gMetadata">
  <tbody>
    <tr>
      <th><?= t("Title:") ?></th>
      <td><?= $item->title; ?></td>
    </tr>
    <tr>
      <th><?= t("Description:") ?></th>
      <td><?= $item->description; ?></td>
    </tr>
    <?  if ($item->id != 1): ?>
    <tr>
      <th><?= t("Name:") ?></th>
      <td><?= $item->name; ?></td>
    </tr>
    <? endif ?>
    <? if ($item->owner): ?>
    <tr>
      <th><?= t("Owner:") ?></th>
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


