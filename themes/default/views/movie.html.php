<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gItem">
  <?= $theme->photo_top() ?>

  <ul id="gPager">
    <li><?= t("%position of %total", array("position" => $position, "total" => $sibling_count)) ?></li>
    <? if ($previous_item): ?>
    <li><span class="ui-icon ui-icon-seek-prev"></span><a href="<?= $previous_item->url() ?>"><?= t("previous") ?></a></li>
    <? endif ?>
    <? if ($next_item): ?>
    <li><a href="<?= $next_item->url() ?>"><?= t("next") ?></a><span class="ui-icon ui-icon-seek-next"></span></li>
    <? endif ?>
  </ul>

  <?= $item->movie_img(array("class" => "gMovie", "id" => "gMovieId-{$item->id}")) ?>

  <div id="gInfo">
    <h1><?= p::purify($item->title) ?></h1>
    <div><?= p::purify($item->description) ?></div>
  </div>

  <script type="text/javascript">
    var ADD_A_COMMENT = "<?= t("Add a comment") ?>";
  </script>
  <?= $theme->photo_bottom() ?>
</div>
