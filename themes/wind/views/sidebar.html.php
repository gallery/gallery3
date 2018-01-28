<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->sidebar_top() ?>
<div id="g-view-menu" class="g-buttonset ui-helper-clearfix">
  <?php if ($page_subtype == "album"):?>
    <?= $theme->album_menu() ?>
  <?php elseif ($page_subtype == "photo") : ?>
    <?= $theme->photo_menu() ?>
  <?php elseif ($page_subtype == "movie") : ?>
    <?= $theme->movie_menu() ?>
  <?php elseif ($page_subtype == "tag") : ?>
    <?= $theme->tag_menu() ?>
  <?php endif ?>
</div>

<?= $theme->sidebar_blocks() ?>
<?= $theme->sidebar_bottom() ?>
