<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumHeader">
  <ul id="gItemMenu" class="sf-menu">
    <li><a href="#" id="gAlbumLink" title="<?= _("View album") ?>"><?= _("Album view") ?></a></li>
    <li><a href="#" id="gHybridLink" title="<?= _("View album in hybrid mode") ?>"><?= _("Hybrid view") ?></a></li>
    <li><?= $theme->album_top() ?></li>
  </ul>

  <h1><?= $item->title_edit ?></h1>
  <div class="gDescription"><?= $item->description_edit ?></div>
</div>

<ul id="gAlbumGrid">
  <? foreach ($children as $i => $child): ?>
  <? $album_class = ""; ?>
  <? if ($child->is_album()): ?>
  <? $album_class = "gAlbum "; ?>
  <? endif ?>
  <li class="gItem <?= $album_class ?>">
    <?= $theme->thumbnail_top($child) ?>
    <a href="<?= url::site("{$child->type}s/{$child->id}") ?>">
      <img id="gPhotoID-<?= $child->id ?>" class="gThumbnail"
           alt="photo" src="<?= $child->thumbnail_url() ?>"
           width="<?= $child->thumbnail_width ?>"
           height="<?= $child->thumbnail_height ?>" />
    </a>
    <h2><?= $child->title_edit ?></h2>
    <?= $theme->thumbnail_bottom($child) ?>
    <ul class="gMetadata">
      <?= $theme->thumbnail_info($child) ?>
    </ul>
  </li>
  <? endforeach ?>
</ul>
<?= $theme->album_bottom() ?>

<?= $theme->pager() ?>
