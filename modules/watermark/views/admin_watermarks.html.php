<? defined("SYSPATH") or die("No direct script access."); ?>
<ul class="gWatermarks">
  <? foreach ($watermarks as $watermark): ?>
  <li>
    <img <?= photo::img_dimensions($watermark->width, $watermark->height, 72) ?>
         src="<?= url::file("var/modules/watermark/$watermark->name") ?>">

   <a href="<?= url::site("admin/watermarks/edit/$watermark->id") ?>" class="gDialogLink"><?= _("edit") ?></a>
   <a href="<?= url::site("admin/watermarks/delete/$watermark->id") ?>"><?= _("delete") ?></a>
  </li>
  <? endforeach ?>
</ul>

<?= $form ?>
