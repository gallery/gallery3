<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-notification-detail">
<ul>
  <? foreach ($subscriptions as $subscription): ?>
  <li id="g-watch-<?= $subscription->id ?>">
    <a href="<?= $subscription->url ?>">
      <?= html::purify($subscription->title) ?>
    </a>
  </li>
  <? endforeach ?>
</ul>
</div>
