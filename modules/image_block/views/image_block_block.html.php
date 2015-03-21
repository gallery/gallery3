<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php foreach ($items as $item): ?>
<div class="g-image-block">
  <a href="<?php echo  url::site("image_block/random/" . $item->id); ?>">
   <?php echo  $item->thumb_img(array("class" => "g-thumbnail")) ?>
  </a>
</div>
<?php endforeach ?>
