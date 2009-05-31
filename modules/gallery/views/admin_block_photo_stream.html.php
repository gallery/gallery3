<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
<? foreach ($photos as $photo): ?>
  <li class="gItem gPhoto">
    <a href="<?= url::site("photos/$photo->id") ?>" title="<?= p::clean($photo->title) ?>">
      <img <?= photo::img_dimensions($photo->width, $photo->height, 72) ?>
        src="<?= $photo->thumb_url() ?>" alt="<?= p::clean($photo->title) ?>" />
    </a>
  </li>
<? endforeach ?>
</ul>
<p>
  <?= t("Recent photos added to your Gallery") ?>
</p>
