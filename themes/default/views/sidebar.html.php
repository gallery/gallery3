<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul id="gViewMenu" class="sf-menu">
  <li><a href="#" id="gFullsizeLink" title="<?= _("View full size image") ?>"><?= _("View full size image") ?></a></li>
  <li><a href="#" id="gAlbumLink" title="<?= _("View album") ?>"><?= _("Album view") ?></a></li>
  <li><a href="#" id="gHybridLink" title="<?= _("View album in hybrid mode") ?>"><?= _("Hybrid view") ?></a></li>
  <li><?= $theme->sidebar_top() ?></li>
</ul>

<?= $theme->sidebar_blocks() ?>
<?= $theme->sidebar_bottom() ?>
