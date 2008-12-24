<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gItemHeader">
  <?= $theme->photo_top() ?>
  <h1><?= $item->title ?></h1>
</div>

<div id="gItem">
  <img id="gPhotoID-<?= $item->id ?>" alt="<?= $item->title ?>" src="<?= $item->resize_url() ?>"
       width="<?= $item->resize_width ?>"
       height="<?= $item->resize_height ?>" />
  <div><?= $item->description ?></div>

  <?= $theme->photo_bottom() ?>
</div>
