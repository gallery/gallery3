<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($items as $item): ?>
<div class="g-image-block">
  <a href="<?= $item->url() ?>">
   <?= $item->thumb_img(array("class" => "g-thumbnail")) ?>
  </a>
</div>
<? endforeach ?>
