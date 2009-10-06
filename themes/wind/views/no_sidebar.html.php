<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-warning">
  <?= t("No active sidebar blocks. <a href=\"%url\">Add blocks</a>",
          array("url" => html::mark_clean(url::site("admin/sidebar")))) ?>
</div>
