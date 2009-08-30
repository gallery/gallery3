<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $album) ? "" : "gViewOnly" ?>"
    ref="<?= $album->id ?>">
  <span class="ui-icon ui-icon-minus">
  </span>
  <span class="gAlbumText
               <?= $selected && $album->id == $selected->id ? "selected" : "" ?>
               "
        ref="<?= $album->id ?>">
    <?= p::clean($album->title) ?>
  </span>
  <ul>
    <? foreach ($album->children(null, 0, array("type" => "album")) as $child): ?>
    <? if ($selected && $child->is_descendant($selected)): ?>
    <?= View::factory("organize_tree.html", array("selected" => $selected, "album" => $child)); ?>
    <? else: ?>
    <li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $child) ? "" : "gViewOnly" ?>"
        ref="<?= $child->id ?>">
      <span class="ui-icon ui-icon-plus">
      </span>
      <span class="gAlbumText" ref="<?= $child->id ?>">
        <?= p::clean($child->title) ?>
      </span>
    </li>
    <? endif ?>
    <? endforeach ?>
  </ul>
</li>

