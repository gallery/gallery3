<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
<? foreach ($panes as $idx => $pane): ?>
  <li><a href="#pane-<?= $idx ?>"><?= $pane["label"] ?></a></li>
<? endforeach?>
</ul>

<? if (count($panes) > 0): ?>
  <? foreach ($panes as $idx => $pane): ?>
    <div id="pane-<?= $idx ?>" class="gOrganizeEditPane ui-tabs-hide"><?= $pane["content"] ?></div>
  <? endforeach?>
<? else: ?>
<div class="gWarning"><?= t("No Edit pages apply to the selected items") ?></div>
<? endif ?>