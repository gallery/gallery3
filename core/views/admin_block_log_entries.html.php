<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <? $map = array(log::INFO => "gInfo", log::WARNING => "gWarning", log::ERROR => "gError") ?>
  <? foreach ($entries as $entry): ?>
  <li class="<?= $map[$entry->severity] ?>">
    <a href="<?= url::site("user/$entry->user_id") ?>"><?= $entry->user->name ?></a>
    <?= date("Y-M-d H:i:s", $entry->timestamp) ?>
    <?= $entry->message ?>
    <?= $entry->html ?>
  </li>
  <? endforeach ?>
</ul>
