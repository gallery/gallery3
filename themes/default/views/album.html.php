<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumHeader">
  <?= $theme->album_top() ?>
  <h1><?= $item->title ?></h1>
  <div class="gDescription"><?= $item->description ?></div>
</div>

<ul id="gAlbumGrid">
  <? foreach ($children as $i => $child): ?>
  <? $album_class = ""; ?>
  <? if ($child->is_album()): ?>
  <? $album_class = "gAlbum "; ?>
  <? endif ?>
  <li class="gItem <?= $album_class ?>">
    <?= $theme->thumb_top($child) ?>
    <a href="<?= url::site("{$child->type}s/{$child->id}") ?>">
      <img id="gPhotoID-<?= $child->id ?>" class="gThumbnail"
           alt="photo" src="<?= $child->thumb_url() ?>"
           width="<?= $child->thumb_width ?>"
           height="<?= $child->thumb_height ?>" />
    </a>
    <h2><?= $child->title ?></h2>
    <?= $theme->thumb_bottom($child) ?>
    <ul class="gMetadata">
      <?= $theme->thumb_info($child) ?>
    </ul>
  </li>
  <? endforeach ?>
</ul>
<?= $theme->album_bottom() ?>

<?= $theme->pager() ?>
