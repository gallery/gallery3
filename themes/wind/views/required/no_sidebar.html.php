<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-message-block">
  <li class="g-warning">
  <? if (BlockManager::get_active("site_sidebar")): ?>
  <?= t("Active sidebar blocks have no content.") ?>
  <? else: ?>
  <?= t("No active sidebar blocks.") ?>
  <? endif ?>
  <a href="<?= URL::site("admin/sidebar") ?>"><?= t("configure blocks") ?></a>
  </li>
</ul>
