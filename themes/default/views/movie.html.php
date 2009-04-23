<?php defined("SYSPATH") or die("No direct script access.") ?>
<script src="<?= url::file("lib/flowplayer.js") ?>" type="text/javascript"></script>
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

  <a id="gMovieId-<?= $item->id ?>"
     href="<?= $item->file_url(true) ?>"
     style="display: block; width: <?= $item->width ?>px; height: <?= $item->height ?>px">
  </a>
  <script>
    flowplayer("gMovieId-<?= $item->id ?>", "<?= url::abs_file("lib/flowplayer.swf") ?>", {
      plugins: {
        h264streaming: {
          url: "<?= url::abs_file("lib/flowplayer.h264streaming.swf") ?>"
        },
        controls: {
          autoHide: 'always',
          hideDelay: 2000,
        }
      }
    })
  </script>

  <div id="gInfo">
    <h1><?= $item->title ?></h1>
    <div><?= $item->description ?></div>
  </div>

  <?= $theme->photo_bottom() ?>
</div>
