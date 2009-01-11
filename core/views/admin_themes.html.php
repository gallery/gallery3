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
  <h1><?= t("Theme Administration") ?></h1>
  <div id="gThemeTabs">
    <?= $menu ?>
  </div>

  <!-- @todo: move this fix into the CSS file -->
  <div style="clear: both"></div>

  <div id="gThemePane">
    <h1> <?= $title ?> </h1>
    <div class="active">
      <h2> <?= t("Selected theme") ?> </h2>
      <div class="theme_block">
        <h3> <?= $themes[$active]->name ?> </h3>
        <img src="<?= url::file("themes/{$active}/thumbnail.png") ?>"
             alt="<?= $themes[$active]->name ?>" />
        <p>
          <?= $themes[$active]->description ?>
        </p>
      </div>
    </div>

    <div class="available">
      <h2> <?= t("Available themes") ?> </h2>
      <? foreach ($themes as $id => $info): ?>
      <? if (!$info->$type) continue ?>
      <? if ($id == $active) continue ?>
      <div class="theme_block gDialogLink" href="<?= url::site("admin/themes/preview/$type/$id") ?>">
        <h3> <?= $info->name ?> </h3>
        <img src="<?= url::file("themes/{$id}/thumbnail.png") ?>"
             alt="<?= $info->name ?>" />
        <p>
          <?= $info->description ?>
        </p>
      </div>
      <? endforeach ?>
    </div>
  </div>
</div>
