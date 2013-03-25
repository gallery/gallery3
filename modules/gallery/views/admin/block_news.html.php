<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <? foreach ($feed as $entry): ?>
  <li>
    <a href="<?= $entry["link"] ?>"><?= $entry["title"] ?></a>
    <p>
      <?= Text::limit_words(strip_tags($entry["description"]), 25); ?>
    </p>
  </li>
  <? endforeach ?>
</ul>
