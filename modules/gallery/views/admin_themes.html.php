<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var select_url = "<?= url::site("admin/themes/choose") ?>";
  select = function(type, id) {
    $.post(select_url, {"type": type, "id": id, "csrf": '<?= $csrf ?>'},
      function() { load(type) });
  }
</script>

<div class="g-block ui-helper-clearfix">
  <h1> <?= t("Theme choice") ?> </h1>
  <p>
    <?= t("Gallery allows you to choose a theme for browsing your Gallery, as well as a special theme for the administration interface.  Click a theme to preview and activate it.") ?>
  </p>

  <div class="g-block-content">
    <div id="g-site-theme">
      <h2> <?= t("Gallery theme") ?> </h2>
      <div class="g-block g-selected ui-helper-clearfix">
        <img src="<?= url::file("themes/{$site}/thumbnail.png") ?>"
             alt="<?= html::clean_attribute($themes[$site]->name) ?>" />
        <h3> <?= $themes[$site]->name ?> </h3>
        <p>
          <?= $themes[$site]->description ?>
        </p>
      </div>

      <h2> <?= t("Available Gallery themes") ?> </h2>
      <div class="g-available">
        <? $count = 0 ?>
        <? foreach ($themes as $id => $info): ?>
        <? if (!$info->site) continue ?>
        <? if ($id == $site) continue ?>
        <div class="g-block ui-helper-clearfix">
          <a href="<?= url::site("admin/themes/preview/site/$id") ?>" class="g-dialog-link" title="<?= t("Theme Preview: %theme_name", array("theme_name" => $info->name))->for_html_attr() ?>">
            <img src="<?= url::file("themes/{$id}/thumbnail.png") ?>"
                 alt="<?= html::clean_attribute($info->name) ?>" />
            <h3> <?= $info->name ?> </h3>
            <p>
              <?= $info->description ?>
            </p>
          </a>
        </div>
        <? $count++ ?>
        <? endforeach ?>

        <? if (!$count): ?>
        <p>
          <?= t("There are no other site themes available.") ?>
        </p>
        <? endif ?>
      </div>
    </div>

    <div id="g-admin-theme">
      <h2> <?= t("Admin theme") ?> </h2>
      <div class="g-block g-selected ui-helper-clearfix">
        <img src="<?= url::file("themes/{$admin}/thumbnail.png") ?>"
             alt="<?= html::clean_attribute($themes[$admin]->name) ?>" />
        <h3> <?= $themes[$admin]->name ?> </h3>
        <p>
          <?= $themes[$admin]->description ?>
        </p>
      </div>

      <h2> <?= t("Available admin themes") ?> </h2>
      <div class="g-available">
        <? $count = 0 ?>
        <? foreach ($themes as $id => $info): ?>
        <? if (!$info->admin) continue ?>
        <? if ($id == $admin) continue ?>
        <div class="g-block ui-helper-clearfix">
          <a href="<?= url::site("admin/themes/preview/admin/$id") ?>" class="g-dialog-link" title="<?= t("Theme Preview: %theme_name", array("theme_name" => $info->name))->for_html_attr() ?>">
            <img src="<?= url::file("themes/{$id}/thumbnail.png") ?>"
                 alt="<?= html::clean_attribute($info->name) ?>" />
            <h3> <?= $info->name ?> </h3>
            <p>
              <?= $info->description ?>
            </p>
          </a>
        </div>
        <? $count++ ?>
        <? endforeach ?>

        <? if (!$count): ?>
        <p>
          <?= t("There are no other admin themes available.") ?>
        </p>
        <? endif ?>
      </div>
    </div>

  </div>
</div>