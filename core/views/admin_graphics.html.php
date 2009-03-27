<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    select_toolkit = function(el) {
      if (!$(this).hasClass("gUnavailable")) {
        window.location = '<?= url::site("admin/graphics/choose/__TK__?csrf=$csrf") ?>'
          .replace("__TK__", $(this).attr("id"));
      }
    };
    $("#gAdminGraphics div.gAvailable .gBlock").click(select_toolkit);
  });

</script>
<div id="gAdminGraphics">
  <h1> <?= t("Graphics Settings") ?> </h1>
  <p>
    <?= t("Gallery needs a graphics toolkit in order to manipulate your photos.  Please choose one from the list below.") ?>
  </p>

  <h2> <?= t("Active Toolkit") ?> </h2>
  <?= $active ?>

  <div class="gAvailable">
    <h2> <?= t("Available Toolkits") ?> </h2>
    <?= $available ?>
  </div>
</div>

