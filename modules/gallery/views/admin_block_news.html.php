<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <?php foreach ($feed as $entry): ?>
  <li>
    <a href="<?php echo  $entry["link"] ?>"><?php echo  $entry["title"] ?></a>
    <p>
      <?php echo  text::limit_words(strip_tags($entry["description"]), 25); ?>
    </p>
  </li>
  <?php endforeach ?>
</ul>
