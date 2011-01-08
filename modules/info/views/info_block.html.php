<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-metadata">
  <? foreach($metadata as $info): ?>
  <li>
    <strong class="caption"><?= $info["label"] ?></strong> <?= $info["value"] ?>
  </li>
  <?  endforeach; ?>
</ul>
