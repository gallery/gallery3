<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $parent->thumb_tag(array(), 25); ?>
<? if (!access::can("edit", $parent) || $source->is_descendant($parent)): ?>
<a href="javascript:load_tree('<?= $parent->id ?>',1)"> <?= $parent->title ?> <?= t("(locked)") ?> </a>
<? else: ?>
<a href="javascript:load_tree('<?= $parent->id ?>',0)"> <?= $parent->title ?></a>
<? endif ?>
<ul id="tree_<?= $parent->id ?>">
  <? foreach ($children as $child): ?>
  <li id="node_<?= $child->id ?>" class="node">
    <?= $child->thumb_tag(array(), 25); ?>
    <? if (!access::can("edit", $child) || $source->is_descendant($child)): ?>
    <a href="javascript:load_tree('<?= $child->id ?>',1)"> <?= $child->title ?> <?= t("(locked)") ?></a>
    <? else: ?>
    <a href="javascript:load_tree('<?= $child->id ?>',0)"> <?= $child->title ?> </a>
    <? endif ?>
  </li>
  <? endforeach ?>
</ul>
