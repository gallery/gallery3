<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gThemes">
  <h1><?= t("Theme Administration") ?></h1>
    <form method="post" id="gThemeAdmin" action="<?= url::site("admin/themes/save") ?>">
      <?= access::csrf_form_field() ?>
      <table><tbody><tr><td>
      <?= t("Current theme") ?><br />
      <a href="#">
      <img src="<?= url::file("themes/{$active}/thumbnail.png") ?>" alt="<?= $themes[$active]->name ?>" />
      </a><br />
      <?= $themes[$active]->description ?><br />
      <input type="radio" name="themes" value="<?= $active ?>" checked="checked">
      <?= $themes[$active]->name ?>
      </td>
      <? foreach ($themes as $id => $theme): ?>
      <? if ($id == $active) continue; ?>
      <td>
      <a href="#">
      <img src="<?= url::file("themes/{$id}/thumbnail.png") ?>" alt="<?= $theme->name ?>" />
      </a><br />
      <?= $theme->description ?><br />
      <input type="radio" name="themes" value="<?= $id ?>"> <?= $theme->name ?>
      </td>
      <? endforeach ?>
      </tr></tbody></table>
      <input type="submit" value="<?= t("Save") ?>"/>
    </form>
    <div id="gThemeDetails"></div>
    
</div>
