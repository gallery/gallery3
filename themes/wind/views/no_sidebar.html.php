<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-message-block">
  <li class="g-warning"><?= t("No active sidebar blocks.<br/>
      <a href=\"%url\">Add blocks</a>",
          array("url" => html::mark_clean(url::site("admin/sidebar")))) ?></li>
</ul>
