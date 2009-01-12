<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var load_url = '<?= url::site("admin/themes/__TYPE__") ?>';
  var load = function(type) {
    $("#gThemePane").load(load_url.replace('__TYPE__', type));
  }

  var select_url = "<?= url::site("admin/themes/choose") ?>";
  select = function(type, id) {
    $.post(select_url, {"type": type, "id": id, "csrf": '<?= access::csrf_token() ?>'},
      function() { load(type) });
  }
</script>
<div id="gAdminThemes">
  <h1> <?= t("Theme Administration") ?> </h1>
  <div id="gThemeTabs">
    <?= $menu ?>
  </div>

  <div id="gThemePane">
    <h1> <?= $title ?> </h1>
    <div id="gSelectedTheme">
      <h2> <?= t("Selected theme") ?> </h2>
      <div class="gBlock">
         <img src="<?= url::file("themes/{$active}/thumbnail.png") ?>"
             alt="<?= $themes[$active]->name ?>" />
        <h3> <?= $themes[$active]->name ?> </h3>
        <p>
          <?= $themes[$active]->description ?>
        </p>
      </div>
    </div>

    <div id="gAvailableThemes">
      <h2> <?= t("Available themes") ?> </h2>
      <p><?= t("Change the look of your Gallery with one of the following available themes. Click to preview and activate.") ?></p>
      <? foreach ($themes as $id => $info): ?>
      <? if (!$info->$type) continue ?>
      <? if ($id == $active) continue ?>
      <div class="gBlock">
        <a href="<?= url::site("admin/themes/preview/$type/$id") ?>" class="gDialogLink" title="<?= t("Theme Preview: {{theme_name}}", array("theme_name" => $info->name)) ?>">
        <h3> <?= $info->name ?> </h3>
        <img src="<?= url::file("themes/{$id}/thumbnail.png") ?>"
             alt="<?= $info->name ?>" />
        <p>
          <?= $info->description ?>
        </p>
        </a>
      </div>
      <? endforeach ?>
    </div>
  </div>
</div>
