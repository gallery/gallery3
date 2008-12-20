<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <li>
    <? printf(_("Version: %s"), "3.0") ?>
  </li>
  <li>
    <? printf(_("Albums: %d"), $album_count) ?>
  </li>
  <li>
    <? printf(_("Photos: %d"), $photo_count) ?>
  </li>
</ul>
