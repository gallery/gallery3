<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($parents as $parent): ?>
<li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $parent) ? "" : "gViewOnly" ?>"
    ref="<?= $parent->id ?>">
  <span class="ui-icon ui-icon-minus">
  </span>
  <span class="gAlbumText" ref="<?= $parent->id ?>">
    <?= p::clean($parent->title) ?>
  </span>
  <ul class="ui-icon-plus">
    <? endforeach ?>

    <? foreach ($peers as $peer): ?>
    <li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $peer) ? "" : "gViewOnly" ?>"
        ref="<?= $peer->id ?>">
      <span class="ui-icon <?= $peer->id == $album->id ? "ui-icon-minus" : "ui-icon-plus" ?>">
      </span>
      <span class="gAlbumText <?= $peer->id == $album->id ? "selected" : "" ?>"
            ref="<?= $peer->id ?>">
        <?= p::clean($peer->title) ?>
      </span>

      <? if ($peer->id == $album->id): ?>
      <ul class="ui-icon-plus">
        <? foreach ($album->children(null, 0, array("type" => "album")) as $child): ?>
        <li class="gOrganizeAlbum ui-icon-left <?= access::can("edit", $child) ? "" : "gViewOnly" ?>"
            ref="<?= $child->id ?>">
          <span class="ui-icon ui-icon-plus">
          </span>
          <span class="gAlbumText"
                ref="<?= $child->id ?>">
            <?= p::clean($child->title) ?>
          </span>
        </li>
        <? endforeach ?>
      </ul>
      <? endif ?>
    </li>
    <? endforeach ?>

    <? foreach ($parents as $parent): ?>
  </ul>
</li>
<? endforeach ?>
