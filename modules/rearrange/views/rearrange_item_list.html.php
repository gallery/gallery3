<? defined("SYSPATH") or die("No direct script access."); ?>
<ul class="jqueryFileTree" style="display: none">
  <? foreach ($children as $item): ?>
    <? if ($item->type == "album"): ?>
      <li class="directory collapsed treeitem" id="<?= $item->id?>">
        <a href="#" rel="<?= $item->id?>"><?= $item->title?></a>
      </li>
    <? else: ?>
      <li class="file item treeitem" id="<?= $item->id?>"><a href="#" rel="<?= $item->id?>"><?= $item->title?></a>
      </li>
    <? endif; ?>
  <? endforeach;?>
</ul>
