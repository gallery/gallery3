<?php defined("SYSPATH") or die("No direct script access."); ?>
<ul class="jqueryFileTree" style="display: none">
  <? foreach ($children as $item): ?>
    <? if ($item->type == "album"): ?>
      <li class="directory <?= $item->children_count() > 0 ? "collapsed" : "" ?>" id="<?= $item->id?>">
        <a href="#" rel="<?= $item->id?>"><?= $item->title?></a>
      </li>
    <? else: ?>
      <li class="file item" id="<?= $item->id?>"><a href="#" rel="<?= $item->id?>"><?= $item->title?></a>
      </li>
    <? endif; ?>
  <? endforeach;?>
</ul>
