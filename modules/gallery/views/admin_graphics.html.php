<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    $(".g-available .g-block").equal_heights();
    select_toolkit = function(el) {
      if (!$(this).hasClass("g-unavailable")) {
        window.location = <?php echo  html::js_string(url::site("admin/graphics/choose/__TK__?csrf=$csrf")) ?>
          .replace("__TK__", $(this).attr("id"));
      }
    };
    $("#g-admin-graphics div.g-available .g-block").click(select_toolkit);
  });
</script>

<div id="g-admin-graphics" class="g-block ui-helper-clearfix">
  <h1> <?php echo  t("Graphics settings") ?> </h1>
  <p>
    <?php echo  t("Gallery needs a graphics toolkit in order to manipulate your photos.  Please choose one from the list below.") ?>
    <?php echo  t("Can't decide which toolkit to choose?  <a href=\"%url\">We can help!</a>", array("url" => "http://codex.galleryproject.org/Gallery3:Choosing_A_Graphics_Toolkit")) ?>
  </p>

  <div class="g-block-content">
    <h2> <?php echo  t("Active toolkit") ?> </h2>
    <?php if ($active == "none"): ?>
    <?php echo  new View("admin_graphics_none.html") ?>
    <?php else: ?>
    <?php echo  new View("admin_graphics_$active.html", array("tk" => $tk->$active, "is_active" => true)) ?>
    <?php endif ?>

    <div class="g-available">
      <h2> <?php echo  t("Available toolkits") ?> </h2>
      <?php foreach (array_keys((array)$tk) as $id): ?>
      <?php if ($id != $active): ?>
      <?php echo  new View("admin_graphics_$id.html", array("tk" => $tk->$id, "is_active" => false)) ?>
      <?php endif ?>
      <?php endforeach ?>
    </div>
  </div>
</div>

