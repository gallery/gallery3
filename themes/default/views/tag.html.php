<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumHeader">
  <h1><?= $tag->name ?></h1>
  <?= $theme->tag_top() ?>
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
<?= $theme->tag_bottom() ?>

<?= $theme->pager() ?>
