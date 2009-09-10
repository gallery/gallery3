<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $parent->thumb_img(array(), 25); ?>
<? if (!access::can("edit", $parent) || $source->contains($parent)): ?>
<a href="javascript:load_tree('<?= $parent->id ?>',1)"> <?= html::clean($parent->title) ?> <?= t("(locked)") ?> </a>
<? else: ?>
<a href="javascript:load_tree('<?= $parent->id ?>',0)"> <?= html::clean($parent->title) ?></a>
<? endif ?>
<ul id="tree_<?= $parent->id ?>">
  <? foreach ($children as $child): ?>
  <li id="node_<?= $child->id ?>" class="node">
    <?= $child->thumb_img(array(), 25); ?>
    <? if (!access::can("edit", $child) || $source->contains($child)): ?>
    <a href="javascript:load_tree('<?= $child->id ?>',1)"> <?= html::clean($child->title) ?> <?= t("(locked)") ?></a>
    <? else: ?>
    <a href="javascript:load_tree('<?= $child->id ?>',0)"> <?= html::clean($child->title) ?> </a>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
