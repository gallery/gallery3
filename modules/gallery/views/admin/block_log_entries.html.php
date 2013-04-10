<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($entries as $entry): ?>
  <li class="<?= GalleryLog::severity_class($entry->severity) ?>" style="direction: ltr">
    <? if ($entry->user->guest): ?>
    </span><?= HTML::clean($entry->user->name) ?></span>
    <? else: ?>
    <a href="<?= UserProfile::url($entry->user->id) ?>"><?= HTML::clean($entry->user->name) ?></a>
    <? endif ?>
    <?= Gallery::date_time($entry->timestamp) ?>
    <?= $entry->message ?>
    <?= $entry->html ?>
  </li>
  <? endforeach ?>
</ul>
