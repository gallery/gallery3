<? defined("SYSPATH") or die("No direct script access."); ?>
<ul class="gWatermarks">
  <? foreach ($watermarks as $watermark): ?>
  <li>
    <img <?= photo::img_dimensions($watermark->width, $watermark->height, 72) ?> src="<?= url::file("var/modules/watermark/$watermark->name") ?>">
  </li>
  <? endforeach ?>
</ul>

<?= $form ?>
