<?php defined("SYSPATH") or die("No direct script access.") ?>
<form action="<?= url::site("search") ?>" id="g-quick-search-form" class="g-short-form">
  <? if (isset($item)): ?>
    <? $album_id = $item->is_album() ? $item->id : $item->parent_id; ?>
  <? else: ?>
    <? $album_id = item::root()->id; ?>
  <? endif; ?>
  <ul>
    <li>
      <? if ($album_id == item::root()->id): ?>
        <label for="g-search"><?= t("Search the gallery") ?></label>
      <? else: ?>
        <label for="g-search"><?= t("Search this album") ?></label>
      <? endif; ?>
      <input type="hidden" name="album" value="<?= $album_id ?>" />
      <input type="text" name="q" id="g-search" class="text" />
    </li>
    <li>
      <input type="submit" value="<?= t("Go")->for_html_attr() ?>" class="submit" />
    </li>
  </ul>
</form>
