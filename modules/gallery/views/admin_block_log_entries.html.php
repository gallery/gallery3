<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($entries as $entry): ?>
  <li class="<?= log::severity_class($entry->severity) ?>" style="direction: ltr">
    <a href="<?= url::site("user/$entry->user_id") ?>"><?= html::clean($entry->user->name) ?></a>
    <?= gallery::date_time($entry->timestamp) ?>
    <?= $entry->message ?>
    <?= $entry->html ?>
  </li>
  <? endforeach ?>
</ul>
