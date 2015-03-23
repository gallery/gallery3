<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-item">
  <?php echo $theme->photo_top() ?>

  <?php echo $theme->paginator() ?>

  <div id="g-movie" class="ui-helper-clearfix">
    <?php echo $theme->resize_top($item) ?>
    <?php echo $item->movie_img(array("class" => "g-movie", "id" => "g-item-id-{$item->id}")) ?>
    <?php echo $theme->resize_bottom($item) ?>
  </div>

  <div id="g-info">
    <h1><?php echo html::purify($item->title) ?></h1>
    <div><?php echo nl2br(html::purify($item->description)) ?></div>
  </div>

  <?php echo $theme->photo_bottom() ?>
</div>
