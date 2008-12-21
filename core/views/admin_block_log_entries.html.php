<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <? foreach ($entries as $entry): ?>
  <li>
    <a href="<?= url::site("user/$entry->user_id") ?>"><?= $entry->user->name ?></a>
    <?= date("Y-M-d H:i:s", $entry->timestamp) ?>
    <?= $entry->message ?>
    <?= $entry->html ?>
  </li>
  <? endforeach ?>
</ul>
