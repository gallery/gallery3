<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-item">
  <?= $theme->photo_top() ?>

  <?= $theme->paginator() ?>

  <div id="g-movie" class="ui-helper-clearfix">
    <?= $item->movie_img(array("class" => "g-movie", "id" => "g-movie-id-{$item->id}")) ?>
    <?= $theme->context_menu($item, "#g-movie-id-{$item->id}") ?>
  </div>

  <div id="g-info">
    <h1><?= html::purify($item->title) ?></h1>
    <div><?= nl2br(html::purify($item->description)) ?></div>
  </div>

  <?= $theme->photo_bottom() ?>
</div>
