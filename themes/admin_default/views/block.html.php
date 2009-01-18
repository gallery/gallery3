<?php defined("SYSPATH") or die("No direct script access.") ?>
<div block_id="<?= $id ?>" id="<?= $css_id ?>" class="gBlock ui-widget-dialog">
  <div class="ui-dialog-titlebar ui-widget-header">
    <? if ($css_id != "gBlockAdder"): ?>
    <a href="<?= url::site("admin/dashboard/remove_block/$id?csrf=" . access::csrf_token()) ?>"
       class="ui-dialog-titlebar-close">
      <span class="ui-icon ui-icon-closethick">remove</span>
    </a>
    <? endif ?>
    <?= $title ?>
  </div>
  <div class="gBlockContent">
    <?= $content ?>
  </div>
</div>
