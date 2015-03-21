<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <?php foreach ($entries as $entry): ?>
  <li class="<?php echo  log::severity_class($entry->severity) ?>" style="direction: ltr">
    <?php if ($entry->user->guest): ?>
    </span><?php echo  html::clean($entry->user->name) ?></span>
    <?php else: ?>
    <a href="<?php echo  user_profile::url($entry->user->id) ?>"><?php echo  html::clean($entry->user->name) ?></a>
    <?php endif ?>
    <?php echo  gallery::date_time($entry->timestamp) ?>
    <?php echo  $entry->message ?>
    <?php echo  $entry->html ?>
  </li>
  <?php endforeach ?>
</ul>
