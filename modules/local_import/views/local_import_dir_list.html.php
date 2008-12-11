<? defined("SYSPATH") or die("No direct script access."); ?>
<? if (!empty($paths)): ?>
<span id="gRemoveDir">Remove</span>
<ul id="gPathList">
  <? foreach ($paths as $id => $path): ?>
  <li id="<?= $id ?>"><?= $path ?></li>
  <? endforeach ?>
</ul>
<? endif ?>
