<?php defined("SYSPATH") or die("No direct script access.") ?>
<div block_id="<?= $id ?>" id="<?= $css_id ?>" class="gBlock ui-widget">
  <div class="ui-dialog-titlebar ui-widget-header ui-helper-clearfix ui-icon-right">
    <? if ($css_id != "gBlockAdder"): ?>
    <a href="<?= url::site("admin/dashboard/remove_block/$id?csrf=$csrf") ?>"
       class="ui-dialog-titlebar-close ui-corner-all">
      <span class="ui-icon ui-icon-closethick">remove</span>
    </a>
    <? endif ?>
    <?= $title ?>
  </div>
  <div class="gBlockContent">
    <?= $content ?>
  </div>
</div>
