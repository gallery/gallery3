<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-album-header">
  <div id="g-album-header-buttons">
    <?= $theme->dynamic_top() ?>
  </div>
  <h1><?= html::clean($title) ?></h1>
</div>

<ul id="g-album-grid" class="ui-helper-clearfix">
  <? foreach ($children as $i => $child): ?>
  <li class="g-item <?= $child->is_album() ? "g-album" : "" ?>">
    <?= $theme->thumb_top($child) ?>
    <a href="<?= $child->url() ?>">
      <img id="g-photo-id-<?= $child->id ?>" class="g-thumbnail"
           alt="photo" src="<?= $child->thumb_url() ?>"
           width="<?= $child->thumb_width ?>"
           height="<?= $child->thumb_height ?>" />
    </a>
    <h2><?= html::purify($child->title) ?></h2>
    <?= $theme->thumb_bottom($child) ?>
    <ul class="g-metadata">
      <?= $theme->thumb_info($child) ?>
    </ul>
  </li>
  <? endforeach ?>
</ul>
<?= $theme->dynamic_bottom() ?>

<?= $theme->paginator() ?>
