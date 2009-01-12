<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="<?= $css_id ?>" class="gBlock ui-widget-dialog">
  <div class="ui-dialog-titlebar ui-widget-header">
    <a href="<?= url::site("admin/dashboard/remove_block/$id?csrf=" . access::csrf_token()) ?>"
       class="ui-dialog-titlebar-close">
      <span class="ui-icon ui-icon-closethick">remove</span>
    </a>
    <?= $title ?>
  </div>
  <div class="gBlockContent">
    <?= $content ?>
  </div>
</div>
