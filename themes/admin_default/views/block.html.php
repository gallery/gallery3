<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="<?= $id ?>" class="gBlock ui-widget-dialog">
  <div class="ui-dialog-titlebar ui-widget-header">
    <a href="#" class="ui-dialog-titlebar-close">
      <span class="ui-icon ui-icon-closethick">close</span>
    </a>
    <?= $title ?>
  </div>
  <div class="gBlockContent">
    <?= $content ?>
  </div>
</div>
