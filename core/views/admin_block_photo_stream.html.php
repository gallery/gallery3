<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  Recent photos added to your Gallery
</p>
<? foreach ($photos as $photo): ?>
<a href="<?= url::site("photos/$photo->id") ?>">
   <img <?= photo::img_dimensions($photo->width, $photo->height, 72) ?>
        src="<?= $photo->thumb_url() ?>" alt="" />
</a>
<? endforeach ?>
