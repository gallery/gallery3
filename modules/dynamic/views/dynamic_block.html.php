<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="gDynamicBlock">
  <ul>
  <? foreach ($albums as $album => $text): ?>
  <li>
  <a href="<?= url::site("dynamic/$album") ?>"><?= t($text) ?></a>
  </li>
  <? endforeach ?>
  </ul>
</div>
