<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumHeader">
  <div id="gAlbumHeaderButtons">
    <?= $theme->tag_top() ?>
  </div>
  <h1><?= $tag->name ?></h1>
</div>

<ul id="gAlbumGrid">
  <? foreach ($children as $i => $child): ?>
  <? $album_class = ""; ?>
  <? if ($child->is_album()): ?>
  <? $album_class = "gAlbum "; ?>
  <? endif ?>
  <li class="gItem <?= $album_class ?>">
    <?= $theme->thumb_top($child) ?>
    <a href="<?= $child->url() ?>">
      <img id="gPhotoId-<?= $child->id ?>" class="gThumbnail"
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
<?= $theme->tag_bottom() ?>

<?= $theme->pager() ?>
