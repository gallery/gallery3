<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
<? foreach ($photos as $photo): ?>
  <li class="g-item g-photo">
    <a href="<?= $photo->url() ?>" title="<?= html::purify($photo->title)->for_html_attr() ?>">
      <img <?= photo::img_dimensions($photo->width, $photo->height, 72) ?>
        src="<?= $photo->thumb_url() ?>" alt="<?= html::purify($photo->title)->for_html_attr() ?>" />
    </a>
  </li>
<? endforeach ?>
</ul>
<p>
  <?= t("Recent photos added to your Gallery") ?>
</p>
