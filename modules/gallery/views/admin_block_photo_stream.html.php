<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
<?php foreach ($photos as $photo): ?>
  <li class="g-item g-photo">
    <a href="<?php echo $photo->url() ?>" title="<?php echo html::purify($photo->title)->for_html_attr() ?>">
      <img <?php echo photo::img_dimensions($photo->width, $photo->height, 72) ?>
        src="<?php echo $photo->thumb_url() ?>" alt="<?php echo html::purify($photo->title)->for_html_attr() ?>" />
    </a>
  </li>
<?php endforeach ?>
</ul>
<p>
  <?php echo t("Recent photos added to your Gallery") ?>
</p>
