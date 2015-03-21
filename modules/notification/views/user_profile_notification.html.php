<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-notification-detail">
<ul>
  <?php foreach ($subscriptions as $subscription): ?>
  <li id="g-watch-<?php echo  $subscription->id ?>">
    <a href="<?php echo  $subscription->url ?>">
      <?php echo  html::purify($subscription->title) ?>
    </a>
  </li>
  <?php endforeach ?>
</ul>
</div>
