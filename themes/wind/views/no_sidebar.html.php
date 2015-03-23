<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-message-block">
  <li class="g-warning">
  <?php if (block_manager::get_active("site_sidebar")): ?>
  <?php echo t("Active sidebar blocks have no content.") ?>
  <?php else: ?>
  <?php echo t("No active sidebar blocks.") ?>
  <?php endif ?>
  <a href="<?php echo url::site("admin/sidebar") ?>"><?php echo t("configure blocks") ?></a>
  </li>
</ul>
