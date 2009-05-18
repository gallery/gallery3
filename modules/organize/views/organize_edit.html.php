<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
<? foreach ($panes as $idx => $pane): ?>
  <li><a href="#pane-<?= $idx ?>"><?= $pane["label"] ?></a></li>
<? endforeach?>
</ul>

<? foreach ($panes as $idx => $pane): ?>
  <div id="pane-<?= $idx ?>" class="gOrganizeEditPane"><?= $pane["content"] ?></div>
<? endforeach?>
