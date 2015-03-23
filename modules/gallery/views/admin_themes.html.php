<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var select_url = "<?php echo url::site("admin/themes/choose") ?>";
  select = function(type, id) {
    $.post(select_url, {"type": type, "id": id, "csrf": '<?php echo $csrf ?>'},
      function() { load(type) });
  }
</script>

<div class="g-block ui-helper-clearfix">
  <h1> <?php echo t("Theme choice") ?> </h1>
  <p>
    <?php echo t("Make your Gallery beautiful <a href=\"%url\">with a new theme</a>!  There are separate themes for the regular site and for the administration interface.  Click a theme below to preview and activate it.", array("url" => "http://codex.galleryproject.org/Category:Gallery_3:Themes")) ?>
  </p>

  <div class="g-block-content">
    <div id="g-site-theme">
      <h2> <?php echo t("Gallery theme") ?> </h2>
      <div class="g-block g-selected ui-helper-clearfix">
        <img src="<?php echo url::file("themes/{$site}/thumbnail.png") ?>"
             alt="<?php echo html::clean_attribute($themes[$site]->name) ?>" />
        <h3> <?php echo $themes[$site]->name ?> </h3>
        <p>
          <?php echo $themes[$site]->description ?>
        </p>
        <?php $v = new View("admin_themes_buttonset.html"); $v->info = $themes[$site]; print $v; ?>
      </div>

      <h2> <?php echo t("Available Gallery themes") ?> </h2>
      <div class="g-available">
        <?php $count = 0 ?>
        <?php foreach ($themes as $id => $info): ?>
        <?php if (!$info->site) continue ?>
        <?php if ($id == $site) continue ?>
        <div class="g-block ui-helper-clearfix">
          <a href="<?php echo url::site("admin/themes/preview/site/$id") ?>" class="g-dialog-link" title="<?php echo t("Theme Preview: %theme_name", array("theme_name" => $info->name))->for_html_attr() ?>">
            <img src="<?php echo url::file("themes/{$id}/thumbnail.png") ?>"
                 alt="<?php echo html::clean_attribute($info->name) ?>" />
            <h3> <?php echo $info->name ?> </h3>
            <p>
              <?php echo $info->description ?>
            </p>
          </a>
          <?php $v = new View("admin_themes_buttonset.html"); $v->info = $info; print $v; ?>
        </div>
        <?php $count++ ?>
        <?php endforeach ?>

        <?php if (!$count): ?>
        <p>
          <?php echo t("There are no other site themes available. <a href=\"%url\">Download one now!</a>", array("url" => "http://codex.galleryproject.org/Category:Gallery_3:Themes")) ?>
        </p>
        <?php endif ?>
      </div>
    </div>

    <div id="g-admin-theme">
      <h2> <?php echo t("Admin theme") ?> </h2>
      <div class="g-block g-selected ui-helper-clearfix">
        <img src="<?php echo url::file("themes/{$admin}/thumbnail.png") ?>"
             alt="<?php echo html::clean_attribute($themes[$admin]->name) ?>" />
        <h3> <?php echo $themes[$admin]->name ?> </h3>
        <p>
          <?php echo $themes[$admin]->description ?>
        </p>
        <?php $v = new View("admin_themes_buttonset.html"); $v->info = $themes[$admin]; print $v; ?>
      </div>

      <h2> <?php echo t("Available admin themes") ?> </h2>
      <div class="g-available">
        <?php $count = 0 ?>
        <?php foreach ($themes as $id => $info): ?>
        <?php if (!$info->admin) continue ?>
        <?php if ($id == $admin) continue ?>
        <div class="g-block ui-helper-clearfix">
          <a href="<?php echo url::site("admin/themes/preview/admin/$id") ?>" class="g-dialog-link" title="<?php echo t("Theme Preview: %theme_name", array("theme_name" => $info->name))->for_html_attr() ?>">
            <img src="<?php echo url::file("themes/{$id}/thumbnail.png") ?>"
                 alt="<?php echo html::clean_attribute($info->name) ?>" />
            <h3> <?php echo $info->name ?> </h3>
            <p>
              <?php echo $info->description ?>
            </p>
          </a>
          <?php $v = new View("admin_themes_buttonset.html"); $v->info = $info; print $v; ?>
        </div>
        <?php $count++ ?>
        <?php endforeach ?>

        <?php if (!$count): ?>
        <p>
          <?php echo t("There are no other admin themes available. <a href=\"%url\">Download one now!</a>", array("url" => "http://codex.galleryproject.org/Category:Gallery_3:Themes")) ?>
        </p>
        <?php endif ?>
      </div>
    </div>

  </div>
</div>
