<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-metadata">
  <?php foreach($metadata as $info): ?>
  <li>
    <strong class="caption"><?php echo $info["label"] ?></strong> <?php echo $info["value"] ?>
  </li>
  <?php  endforeach; ?>
</ul>
