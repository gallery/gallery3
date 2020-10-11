<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php // @todo Set hover on AlbumGrid list items for guest users ?>
<div id="g-info">
  <?= $theme->album_top() ?>
  <h1><?= html::purify($item->title) ?></h1>
  <div class="g-description"><?= nl2br(html::purify($item->description)) ?></div>
</div>

<ul id="g-album-grid" class="ui-helper-clearfix">
<?php if (count($children)): ?>
  <?php foreach ($children as $i => $child): ?>
    <?php if ($child->is_album()): ?>
      <?php $item_class = "g-album"; ?>
    <?php elseif ($child->is_movie()): ?>
      <?php $item_class = "g-movie"; ?>
    <?php else: ?>
      <?php $item_class = "g-photo"; ?>
    <?php endif ?>
  <li id="g-item-id-<?= $child->id ?>" class="g-item <?= $item_class ?>">
    <?= $theme->thumb_top($child) ?>
    <a href="<?= $child->url() ?>">
      <?php if ($child->has_thumb()): ?>
      <?= $child->thumb_img(array("class" => "g-thumbnail")) ?>
      <?php endif ?>
    </a>
    <?= $theme->thumb_bottom($child) ?>
    <?= $theme->context_menu($child, "#g-item-id-{$child->id} .g-thumbnail") ?>
    <h2><span class="<?= $item_class ?>"></span>
      <a href="<?= $child->url() ?>"><?= html::purify($child->title) ?></a></h2>
    <ul class="g-metadata">
      <?= $theme->thumb_info($child) ?>
    </ul>
  </li>
  <?php endforeach ?>
<?php else: ?>
  <?php if ($user->admin || access::can("add", $item)): ?>
  <?php $addurl = url::site("uploader/index/$item->id") ?>
  <li><?= t("There aren't any photos here yet! <a %attrs>Add some</a>.",
            array("attrs" => html::mark_clean("href=\"$addurl\" class=\"g-dialog-link\""))) ?></li>
  <?php else: ?>
  <li><?= t("There aren't any photos here yet!") ?></li>
  <?php endif; ?>
<?php endif; ?>
</ul>
<?= $theme->album_bottom() ?>

<?= $theme->paginator() ?>
