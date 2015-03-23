<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul id="g-feeds">
<?php foreach($feeds as $url => $title): ?>
  <li style="clear: both;">
    <span class="ui-icon-left">
    <a href="<?php echo rss::url($url) ?>">
      <span class="ui-icon ui-icon-signal-diag"></span>
      <?php echo html::purify($title) ?>
    </a>
    </span>
  </li>
<?php endforeach ?>
</ul>
