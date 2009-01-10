<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gThemes">
  <h1><?= t("Theme Administration") ?></h1>
    <form method="post" id="gThemeAdmin" action="<?= url::site("admin/themes/save") ?>">
      <?= access::csrf_form_field() ?>
      <div id="gThemeTabs">
        <ul>
          <li><a href="#gtRegular"><span>Regular</span></a></li>
          <li><a href="#gtAdmin"><span>Admin</span></a></li>
        </ul>
        <div id="gtRegular">
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
        </div>
        <div id="gtAdmin">
          <table><tbody><tr><td>
          <?= t("Current theme") ?><br />
          <a href="#">
          <img src="<?= url::file("themes/{$active_admin}/thumbnail.png") ?>"
                        alt="<?= $themes[$active_admin]->name ?>" />
          </a><br />
          <?= $admin_themes[$active_admin]->description ?><br />
          <input type="radio" name="admin_themes" value="<?= $active_admin ?>" checked="checked">
          <?= $admin_themes[$active_admin]->name ?>
          </td>
          <? foreach ($admin_themes as $id => $theme): ?>
          <? if ($id == $active_admin) continue; ?>
          <td>
          <a href="#">
          <img src="<?= url::file("themes/{$id}/thumbnail.png") ?>" alt="<?= $theme->name ?>" />
          </a><br />
          <?= $theme->description ?><br />
          <input type="radio" name="admin_themes" value="<?= $id ?>"> <?= $theme->name ?>
          </td>
          <? endforeach ?>
          </tr></tbody></table>
        </div>
        </div>
      </div>
      <input type="submit" value="<?= t("Save") ?>"/>
    </form>
    <div id="gThemeDetails">
      <?= $themes[$active]->details ?>  
    </div>
    
</div>
