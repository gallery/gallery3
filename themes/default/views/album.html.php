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
  <li id="g<?= $child->id ?>" class="gItem <?= $album_class ?>">
    <?= $theme->thumb_top($child) ?>
    <a href="<?= $child->url() ?>">
      <img id="gPhotoId-<?= $child->id ?>" class="gThumbnail"
           alt="photo" src="<?= $child->thumb_url() ?>"
           width="<?= $child->thumb_width ?>"
           height="<?= $child->thumb_height ?>" />
    </a>
    <h2><a href="<?= $child->url() ?>"><?= $child->title ?></a></h2>
    <?= $theme->thumb_bottom($child) ?>
    <ul class="gMetadata">
      <?= $theme->thumb_info($child) ?>
    </ul>
  </li>
  <? endforeach ?>
</ul>
<?= $theme->album_bottom() ?>

<?= $theme->pager() ?>
