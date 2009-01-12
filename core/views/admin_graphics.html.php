<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    select_toolkit = function(el) {
      if (!$(this).hasClass("selected")) {
        window.location = '<?= url::site("admin/graphics/choose/__TK__?csrf=" . access::csrf_token()) ?>'
          .replace("__TK__", $(this).attr("id"));
      }
    };
    $("#gAdminGraphics table tr").click(select_toolkit);
  });
</script>

<h1> <?= t("Graphics Settings") ?> </h1>
<p>
  <?= t("Gallery needs a graphics toolkit in order to manipulate your photos.  Please choose one from the list below.") ?>
</p>

<h2> <?= t("Graphics Toolkits") ?> </h2>

<table id="gAdminGraphics">
  <tr id="gd" <?= ($active == "gd") ? "class=\"selected\"" : "" ?>>
    <td valign="top">
      <img width="170" height="110" src="http://www.libgd.org/skins/libgd/gdlogosmall.png" alt="<? t("Visit the GD lib project site") ?>" />
    </td>
    <td>
      <h3> <?= t("GD") ?> </h3>
      <p>
        <?= t("The GD graphics library is an extension to PHP commonly installed most webservers.  Please refer to the {{link_start}}GD website{{link_end}} for more information.",
            array("link_start" => "<a href=\"http://www.boutell.com/gd/\">", "link_end" => "</a>")) ?>
      </p>
      <? if ($tk->gd["GD Version"] && function_exists('imagerotate')): ?>
      <p class="gSuccess">
        <?= t("You have GD version {{version}}.", array("version" => $tk->gd["GD Version"])) ?>
      </p>
      <? elseif ($tk->gd["GD Version"]): ?>
      <p class="gWarning">
        <?= t("You have GD version {{version}}, but it lacks image rotation.",
            array("version" => $tk->gd["GD Version"])) ?>
      </p>
      <? else: ?>
      <p class="gInfo">
        <?= t("You do not have GD installed.") ?>
      </p>
      <? endif ?>
    </td>
  </tr>

    <tr id="imagemagick" <?= ($active == "imagemagick") ? "class=\"selected\"" : "" ?>>
      <td valign="top">
        <img width="114" height="118" src="http://www.imagemagick.org/image/logo.jpg" alt="<? t("Visit the ImageMagick project site") ?>" />
      </td>
      <td>
        <h3> <?= t("ImageMagick") ?> </h3>
        <p>
          <?= t("ImageMagick is a standalone graphics program available on most Linux systems.  Please refer to the {{link_start}}ImageMagick website{{link_end}} for more information.",
              array("link_start" => "<a href=\"http://www.imagemagick.org/\">", "link_end" => "</a>")) ?>
        </p>
        <? if ($tk->imagemagick): ?>
        <p class="gSuccess">
          <?= t("You have ImageMagick installed in {{path}}", array("path" => $tk->imagemagick)) ?>
        </p>
        <? else: ?>
        <p class="gInfo">
          <?= t("ImageMagick is not available on your system.") ?>
        </p>
        <? endif ?>
      </td>
    </tr>

    <tr id="graphicsmagick" <?= ($active == "graphicsmagick") ? "class=\"selected\"" : "" ?>>
      <td valign="top">
        <img width="107" height="76" src="http://www.graphicsmagick.org/images/gm-107x76.png" alt="<? t("Visit the GraphicsMagick project site") ?>" />
      </td>
      <td>
        <h3> <?= t("GraphicsMagick") ?> </h3>
        <p>
          <?= t("GraphicsMagick is a standalone graphics program available on most Linux systems.  Please refer to the {{link_start}}GraphicsMagick website{{link_end}} for more information.",
              array("link_start" => "<a href=\"http://www.graphicsmagick.org/\">", "link_end" => "</a>")) ?>
        </p>
        <? if ($tk->graphicsmagick): ?>
        <p class="gSuccess">
          <?= t("You have GraphicsMagick installed in {{path}}", array("path" => $tk->graphicsmagick)) ?>
        </p>
        <? else: ?>
        <p class="gInfo">
          <?= t("GraphicsMagick is not available on your system.") ?>
        </p>
        <? endif ?>
    </td>
  </tr>
</table>
