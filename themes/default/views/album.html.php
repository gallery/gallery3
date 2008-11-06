<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumGrid" class="gAlbumView">
  <div id="gAlbumGridHeader">
    <h1><?= $item->title ?></h1>
    <span class="understate"><?= $item->description ?></span>
    <a href="#" id="gSlideshowLink" class="buttonlink">Slideshow</a>
  </div>

  <? foreach ($children as $i => $child): ?>
  <? if ($child->is_album()): ?>
  <div class="gAlbumContainer gAlbum <?= text::alternate("first", "", "") ?>">
    <a href="<?= url::site("album/{$child->id}") ?>">
      <img id="photo-id-<?= $child->id ?>" class="photo"
           alt="photo" src="<?= $child->thumbnail_url() ?>"
           width="<?= $child->thumbnail_width ?>"
           height="<?= $child->thumbnail_height ?>" />
    </a>
    <h2>Album title</h2>
    <ul class="gMetadata">
      <li>Views: 321</li>
      <li>By: <a href="#">username</a></li>
    </ul>
  </div>
  <? else: ?>
  <div class="gItemContainer <?= text::alternate("first", "", "") ?>">
    <a href="<?= url::site("photo/{$child->id}") ?>">
      <img id="photo-id-<?= $child->id ?>" class="photo"
           alt="photo" src="<?= $child->thumbnail_url() ?>"
           width="<?= $child->thumbnail_width ?>"
           height="<?= $child->thumbnail_height ?>" />
    </a>
    <h2><?= $child->title ?></h2>
  </div>
  <? endif ?>
  <? endforeach ?>

  <div id="gPagination">
    Items 1-10 of 34
    <span class="first_inactive">first</span>
    <span class="previous_inactive">previous</span>
    <a href="#" class="next">next</a>
    <a href="#" class="last">last</a>
  </div>
</div>
