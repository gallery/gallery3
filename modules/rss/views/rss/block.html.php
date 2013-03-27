<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul id="g-feeds">
<? foreach($feeds as $url => $title): ?>
  <li style="clear: both;">
    <span class="ui-icon-left">
    <a href="<?= rss::url($url) ?>">
      <span class="ui-icon ui-icon-signal-diag"></span>
      <?= html::purify($title) ?>
    </a>
    </span>
  </li>
<? endforeach ?>
</ul>
