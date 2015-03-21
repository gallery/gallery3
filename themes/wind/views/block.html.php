<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php if ($anchor): ?>
<a name="<?php echo  $anchor ?>"></a>
<?php endif ?>
<div id="<?php echo  $css_id ?>" class="g-block">
  <h2><?php echo  $title ?></h2>
  <div class="g-block-content">
    <?php echo  $content ?>
  </div>
</div>
