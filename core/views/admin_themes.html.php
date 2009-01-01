<? defined("SYSPATH") or die("No direct script access.") ?>
<div id="gThemes">
  <h1><?= _("Theme Administration") ?></h2>
    <p><?= _("These are the themes in your system") ?></p>
    <form method="post" action="<?= url::site("admin/themes/save") ?>">
    <?= access::csrf_form_field() ?>
    <? foreach ($themes as $theme): ?>
    <input type="radio" name="theme" value="<?= $theme ?>"
    <? if ($theme == $active): ?> checked="checked" <? endif ?> /><?= $theme ?>
    <? endforeach ?>
    <input type="submit" value="<?= _("Save") ?>"/>
    </form>
</div>
