<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
<? foreach ($photos as $photo): ?>
  <li class="gItem gPhoto">
    <a href="<?= url::site("photos/$photo->id") ?>" title="<?= $photo->title ?>">
      <img <?= photo::img_dimensions($photo->width, $photo->height, 72) ?>
        src="<?= $photo->thumb_url() ?>" alt="<?= $photo->title ?>" />
    </a>
  </li>
<? endforeach ?>
</ul>
<p>
  <?= t("Recent photos added to your Gallery") ?>
</p>
