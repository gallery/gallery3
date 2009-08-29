<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $album) ? "" : "gViewOnly" ?>"
    ref="<?= $album->id ?>">
  <span class="ui-icon ui-icon-minus">
  </span>
  <span class="gAlbumText" ref="<?= $album->id ?>">
    <?= p::clean($album->title) ?>
  </span>
  <ul>
    <? foreach ($album->children(null, 0, array("type" => "album")) as $child): ?>
    <li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $child) ? "" : "gViewOnly" ?>"
        ref="<?= $child->id ?>">
      <span class="ui-icon ui-icon-plus">
      </span>
      <span class="gAlbumText" ref="<?= $child->id ?>">
        <?= p::clean($child->title) ?>
      </span>
    </li>
    <? endforeach ?>
  </ul>
</li>

