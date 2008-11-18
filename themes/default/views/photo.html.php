<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gItem">
  <a href="" class="gButtonLink">Full size (1024x768)</a>
  <a href="" class="gButtonLink">Slideshow</a>

  <img id="gPhotoID-<?= $item->id ?>" alt="photo" src="<?= $item->resize_url() ?>"
       width="<?= $item->resize_width ?>"
       height="<?= $item->resize_height ?>" />
  <h1><?= $item->title_edit ?></h1>
  <div><?= $item->description_edit ?></div>

  <? if (module::is_installed("comment")): ?>
    <?= comment::block($theme, true); ?>
  <? endif ?>
</div>
