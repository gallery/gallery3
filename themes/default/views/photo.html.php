<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gItem">
  <a href="" class="gButtonLink">
    <?= sprintf(_("Full size (%dx%d)"), $item->width, $item->height) ?>
  </a>
  <?= $theme->photo_top() ?>

  <img id="gPhotoID-<?= $item->id ?>" alt="photo" src="<?= $item->resize_url() ?>"
       width="<?= $item->resize_width ?>"
       height="<?= $item->resize_height ?>" />
  <h1><?= $item->title_edit ?></h1>
  <div><?= $item->description_edit ?></div>

  <?= $theme->photo_bottom() ?>
</div>
