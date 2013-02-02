<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    $(".g-available .g-block").equal_heights();
    select_toolkit = function(el) {
      if (!$(this).hasClass("g-unavailable")) {
        window.location = <?= html::js_string(url::site("admin/graphics/choose/__TK__?csrf=$csrf")) ?>
          .replace("__TK__", $(this).attr("id"));
      }
    };
    $("#g-admin-graphics div.g-available .g-block").click(select_toolkit);
  });
</script>

<div id="g-admin-graphics" class="g-block ui-helper-clearfix">
  <h1> <?= t("Graphics settings") ?> </h1>
  <p>
    <?= t("Gallery needs a graphics toolkit in order to manipulate your photos.  Please choose one from the list below.") ?>
    <?= t("Can't decide which toolkit to choose?  <a href=\"%url\">We can help!</a>", array("url" => "http://codex.galleryproject.org/Gallery3:Choosing_A_Graphics_Toolkit")) ?>
  </p>

  <div class="g-block-content">
    <h2> <?= t("Active toolkit") ?> </h2>
    <? if ($active == "none"): ?>
    <?= new View("admin_graphics_none.html") ?>
    <? else: ?>
    <?= new View("admin_graphics_$active.html", array("tk" => $tk->$active, "is_active" => true)) ?>
    <? endif ?>

    <div class="g-available">
      <h2> <?= t("Available toolkits") ?> </h2>
      <? foreach (array_keys((array)$tk) as $id): ?>
      <? if ($id != $active): ?>
      <?= new View("admin_graphics_$id.html", array("tk" => $tk->$id, "is_active" => false)) ?>
      <? endif ?>
      <? endforeach ?>
    </div>
  </div>

  <div id="g-admin-graphics-settings" class="g-block ui-helper-clearfix">
    <h2> <?= t("Other settings") ?> </h2>
    <p>
      <?= t("By default, Gallery preserves formats when generating resize and thumbnail images.") ?>
      <?= t("This means that a PNG full-size image will have PNG resize and thumbnail images.") ?>
    </p>
    <p>
      <?= t("Alternatively, Gallery can make all resize or thumbnail images JPG.") ?>
      <?= t("These can be much smaller: for example, a typical photographic JPG is 5-10x smaller than a PNG.") ?>
      <?= t("This reduces page load time while still preserving the full-size image in its original format.") ?>
    </p>
    <p>
      <?= t("Changing these settings will temporarily put the site into maintenance mode (if not already in maintenance mode).") ?>
      <?= t("Once finished, it will return maintenance mode to its original state and mark all affected images for rebuild.") ?>
    </p>
    <?= $form ?>
  </div>
</div>
