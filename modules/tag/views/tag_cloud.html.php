<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <?php foreach ($tags as $tag): ?>
  <li class="size<?php echo (int)(($tag->count / $max_count) * 7) ?>">
    <span><?php echo  $tag->count ?> photos are tagged with </span>
    <a href="<?php echo  $tag->url() ?>"><?php echo  html::clean($tag->name) ?></a>
  </li>
  <?php endforeach ?>
</ul>
