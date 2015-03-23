<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php echo $theme->sidebar_top() ?>
<div id="g-view-menu" class="g-buttonset ui-helper-clearfix">
  <?php if ($page_subtype == "album"):?>
    <?php echo $theme->album_menu() ?>
  <?php elseif ($page_subtype == "photo") : ?>
    <?php echo $theme->photo_menu() ?>
  <?php elseif ($page_subtype == "movie") : ?>
    <?php echo $theme->movie_menu() ?>
  <?php elseif ($page_subtype == "tag") : ?>
    <?php echo $theme->tag_menu() ?>
  <?php endif ?>
</div>

<?php echo $theme->sidebar_blocks() ?>
<?php echo $theme->sidebar_bottom() ?>
