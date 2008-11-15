<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumHeader">
  <h1><?= $item->title_edit ?></h1>
  <span class="gUnderState"><?= $item->description_edit ?></span>
  <a href="#" id="gSlideshowLink" class="gButtonLink">Slideshow</a>
</div>

<ul id="gAlbumGrid">
  <? foreach ($children as $i => $child): ?>
  <? $album_class = ""; ?>
  <? if ($child->is_album()): ?>
  <? $album_class = "gAlbum "; ?>
  <? endif ?>
  <li class="gItem <?= $album_class ?>">
    <a href="<?= url::site("{$child->type}/{$child->id}") ?>">
      <img id="gPhotoID-<?= $child->id ?>" class="gThumbnail"
           alt="photo" src="<?= $child->thumbnail_url() ?>"
           width="<?= $child->thumbnail_width ?>"
           height="<?= $child->thumbnail_height ?>" />
    </a>
    <h2><?= $child->title_edit ?></h2>
    <ul class="gMetadata">
      <li>Views: 321</li>
<?
  try {
      echo "<li>" . _("By: ") . '<a href="#">' . $child->owner->name . "</a></li>";
  } catch(Exception $e) {}
?>
    </ul>
  </li>
  <? endforeach ?>
</ul>

<?= $theme->pager() ?>
