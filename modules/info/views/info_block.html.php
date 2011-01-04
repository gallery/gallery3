<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-metadata">
  <? foreach($metadata as $k => $v): ?>
  <li>
    <strong class="caption"><?= $v["label"] ?></strong> <?= $v["value"] ?>
  </li>
  <?  endforeach; ?>
</ul>
