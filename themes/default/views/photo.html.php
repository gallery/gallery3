<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gItemHeader">
  <?= $theme->photo_top() ?>
  <h1><?= $item->title ?></h1>
</div>

<div id="gItem">
  <img id="gPhotoId-<?= $item->id ?>" alt="<?= $item->title ?>" src="<?= $item->resize_url() ?>"
       width="<?= $item->resize_width ?>"
       height="<?= $item->resize_height ?>" />
  <div><?= $item->description ?></div>

  <?= $theme->photo_bottom() ?>
</div>

<? if ($previous_item): ?>
<a href="<?= $previous_item->url() ?>"><?= _("previous") ?></a>
<? endif ?>

<?= sprintf(_("Viewing photo %d of %d"), $position, $sibling_count) ?>

<? if ($next_item): ?>
<a href="<?= $next_item->url() ?>"><?= _("next") ?></a>
<? endif ?>


