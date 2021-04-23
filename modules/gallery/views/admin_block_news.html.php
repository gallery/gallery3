<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <?php foreach ($feed as $entry): ?>
  <li>
    <a href="<?= $entry["link"] ?>"><?= $entry["title"] ?></a>
    <p>
      <?= text::limit_words(strip_tags($entry["description"]), 25); ?>
    </p>
  </li>
  <?php endforeach ?>
</ul>
