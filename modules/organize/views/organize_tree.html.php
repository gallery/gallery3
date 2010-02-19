<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="g-organize-album ui-icon-left <?= access::can("edit", $album) ? "" : "g-view-only" ?>"
    ref="<?= $album->id ?>">
  <span class="ui-icon ui-icon-minus g-left">
  </span>
  <span class="g-organize-album-text <?= $selected && $album->id == $selected->id ? "ui-state-focus" : "" ?>"
        ref="<?= $album->id ?>">
    <?= html::clean($album->title) ?>
  </span>
  <? $child_albums = $album->viewable()->children(null, null, array(array("type", "=", "album"))); ?>
  <? if (!empty($child_albums)): ?>
  <ul>
  <? foreach ($child_albums as $child): ?>
    <? if ($selected && $child->contains($selected)): ?>
    <?= View::factory("organize_tree.html", array("selected" => $selected, "album" => $child)); ?>
    <? else: ?>
    <li class="g-organize-album ui-icon-left <?= access::can("edit", $child) ? "" : "g-view-only" ?>"
        ref="<?= $child->id ?>">
      <span class="ui-icon ui-icon-plus g-left"></span>
      <span class="g-organize-album-text <?= $selected && $child->id == $selected->id ? "ui-state-focus" : "" ?>" ref="<?= $child->id ?>">
        <?= html::clean($child->title) ?>
      </span>
    </li>
    <? endif ?>
    <? endforeach ?>
  </ul>
  <? endif ?>
</li>

