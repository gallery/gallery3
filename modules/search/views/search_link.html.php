<?php defined("SYSPATH") or die("No direct script access.") ?>
<form action="<?= url::site("search") ?>" id="g-quick-search-form" class="g-short-form">
  <?php if (isset($item)): ?>
    <?php $album_id = $item->is_album() ? $item->id : $item->parent_id; ?>
  <?php else: ?>
    <?php $album_id = item::root()->id; ?>
  <?php endif; ?>
  <ul>
    <li>
      <?php if ($album_id == item::root()->id): ?>
        <label for="g-search"><?= t("Search the gallery") ?></label>
      <?php else: ?>
        <label for="g-search"><?= t("Search this album") ?></label>
      <?php endif; ?>
      <input type="hidden" name="album" value="<?= $album_id ?>" />
      <input type="text" name="q" id="g-search" class="text" />
    </li>
    <li>
      <input type="submit" value="<?= t("Go")->for_html_attr() ?>" class="submit" />
    </li>
  </ul>
</form>
