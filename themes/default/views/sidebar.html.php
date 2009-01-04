<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gViewMenu">
<? if ($page_type == "album"):?>
  <?= $theme->album_menu() ?>
<? elseif ($page_type == "photo") : ?>
  <?= $theme->photo_menu() ?>
<? endif ?>
</div>

<?= $theme->sidebar_blocks() ?>
<?= $theme->sidebar_bottom() ?>
