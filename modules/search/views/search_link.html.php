<?php defined("SYSPATH") or die("No direct script access.") ?>
<form action="<?= url::site("search") ?>" id="g-quick-search-form" class="g-short-form">
  <ul>
    <li>
      <label for="g-search"><?= t("Search the gallery") ?></label>
      <input type="text" name="q" id="g-search" class="text" />
    </li>
    <li>
      <input type="submit" value="<?= t("Go")->for_html_attr() ?>" class="submit" />
    </li>
  </ul>
  <? if (isset($item) && $item instanceof Item_Model_Core): ?>
    <? if ($item->is_album ()): ?>
      <input type="hidden" name="album" value="<?= $item->id ?>" />
    <? else: ?>
      <input type="hidden" name="album" value="<?= $item->parent_id ?>" />
    <? endif; ?>
  <? endif; ?>
</form>
