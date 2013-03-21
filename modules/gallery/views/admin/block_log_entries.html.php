<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($entries as $entry): ?>
  <li class="<?= log::severity_class($entry->severity) ?>" style="direction: ltr">
    <? if ($entry->user->guest): ?>
    </span><?= html::clean($entry->user->name) ?></span>
    <? else: ?>
    <a href="<?= user_profile::url($entry->user->id) ?>"><?= html::clean($entry->user->name) ?></a>
    <? endif ?>
    <?= gallery::date_time($entry->timestamp) ?>
    <?= $entry->message ?>
    <?= $entry->html ?>
  </li>
  <? endforeach ?>
</ul>
