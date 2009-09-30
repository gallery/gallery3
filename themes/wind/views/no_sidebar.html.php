<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="gWarning">
  <?= t("No active sidebar panels. <a href=\"%url\">Add panels</a>",
          array("url" => html::mark_clean(url::site("admin/sidebar")))) ?>
</div>
