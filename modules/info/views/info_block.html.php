<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-metadata">
  <?php foreach($metadata as $info): ?>
  <li>
    <strong class="caption"><?= $info["label"] ?></strong> <?= $info["value"] ?>
  </li>
  <?php  endforeach; ?>
</ul>
