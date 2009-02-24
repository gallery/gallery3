<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul id="gFeeds">
<? foreach($feeds as $text => $url): ?>
  <li style="clear: both;">
    <span class="ui-icon-left">
    <a href="<?= $url ?>">
      <span class="ui-icon ui-icon-signal-diag"></span>
      <?= $text ?>
    </a>
    </span>
  </li>
<? endforeach ?>
</ul>