<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php if ($anchor): ?>
<a name="<?php echo  $anchor ?>"></a>
<?php endif ?>
<div block_id="<?php echo  $id ?>" id="<?php echo  $css_id ?>" class="g-block ui-widget">
  <div class="ui-dialog-titlebar ui-widget-header ui-helper-clearfix ui-icon-right">
    <?php if ($css_id != "g-block-adder"): ?>
    <a href="<?php echo  url::site("admin/dashboard/remove_block/$id?csrf=$csrf") ?>"
       class="ui-dialog-titlebar-close ui-corner-all">
      <span class="ui-icon ui-icon-closethick">remove</span>
    </a>
    <?php endif ?>
    <?php echo  $title ?>
  </div>
  <div class="g-block-content">
    <?php echo  $content ?>
  </div>
</div>
