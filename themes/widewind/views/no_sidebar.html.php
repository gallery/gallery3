<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-message-block">
  <li class="g-warning">
  <?php if (block_manager::get_active("site_sidebar")): ?>
  <?= t("Active sidebar blocks have no content.") ?>
  <?php else: ?>
  <?= t("No active sidebar blocks.") ?>
  <?php endif ?>
  <a href="<?= url::site("admin/sidebar") ?>"><?= t("configure blocks") ?></a>
  </li>
</ul>
