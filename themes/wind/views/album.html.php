<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php // @todo Set hover on AlbumGrid list items for guest users ?>
<div id="g-info">
  <?php echo $theme->album_top() ?>
  <h1><?php echo html::purify($item->title) ?></h1>
  <div class="g-description"><?php echo nl2br(html::purify($item->description)) ?></div>
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
  <li id="g-item-id-<?php echo $child->id ?>" class="g-item <?php echo $item_class ?>">
    <?php echo $theme->thumb_top($child) ?>
    <a href="<?php echo $child->url() ?>">
      <?php if ($child->has_thumb()): ?>
      <?php echo $child->thumb_img(array("class" => "g-thumbnail")) ?>
      <?php endif ?>
    </a>
    <?php echo $theme->thumb_bottom($child) ?>
    <?php echo $theme->context_menu($child, "#g-item-id-{$child->id} .g-thumbnail") ?>
    <h2><span class="<?php echo $item_class ?>"></span>
      <a href="<?php echo $child->url() ?>"><?php echo html::purify($child->title) ?></a></h2>
    <ul class="g-metadata">
      <?php echo $theme->thumb_info($child) ?>
    </ul>
  </li>
  <?php endforeach ?>
<?php else: ?>
  <?php if ($user->admin || access::can("add", $item)): ?>
  <?php $addurl = url::site("uploader/index/$item->id") ?>
  <li><?php echo t("There aren't any photos here yet! <a %attrs>Add some</a>.",
            array("attrs" => html::mark_clean("href=\"$addurl\" class=\"g-dialog-link\""))) ?></li>
  <?php else: ?>
  <li><?php echo t("There aren't any photos here yet!") ?></li>
  <?php endif; ?>
<?php endif; ?>
</ul>
<?php echo $theme->album_bottom() ?>

<?php echo $theme->paginator() ?>
