<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($anchor): ?>
<a name="<?= $anchor ?>"></a>
<? endif ?>
<div block_id="<?= $id ?>" id="<?= $css_id ?>" class="g-block ui-widget">
  <div class="ui-dialog-titlebar ui-widget-header ui-helper-clearfix ui-icon-right">
    <? if ($css_id != "g-block-adder"): ?>
    <a href="<?= url::site("admin/dashboard/remove_block/$id?csrf=$csrf") ?>"
       class="ui-dialog-titlebar-close ui-corner-all">
      <span class="ui-icon ui-icon-closethick">remove</span>
    </a>
    <? endif ?>
    <?= $title ?>
  </div>
  <div class="g-block-content">
    <?= $content ?>
  </div>
</div>
