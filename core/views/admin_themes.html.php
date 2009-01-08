<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gThemes">
  <h1><?= t("Theme Administration") ?></h1>
    <p>
      <?= t("These are the themes in your system") ?>
    </p>
    <form method="post" action="<?= url::site("admin/themes/save") ?>">
      <?= access::csrf_form_field() ?>
      <? foreach ($themes as $theme): ?>
      <input type="radio" name="theme" value="<?= $theme ?>"
             <? if ($theme == $active): ?> checked="checked" <? endif ?>
             />
      <?= $theme ?>
      <? endforeach ?>
      <input type="submit" value="<?= t("Save") ?>"/>
    </form>
</div>
