<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gGraphics">
  <h1> <?= t("Graphics Settings") ?> </h1>
  <p>
    <?= t("Gallery needs a graphics toolkit in order to manipulate your photos.  Please choose one from the list below.") ?>
  </p>

  <form method="post" action="<?= url::site("admin/graphics/save") ?>">
    <?= access::csrf_form_field() ?>
    <h2> <?= t("Graphics Toolkits") ?> </h2>
    <table>
      <tr>
        <td valign="top" style="width: 100px">
          <center>
            <input type="radio" name="graphics_toolkit" value="gd"
                   <? if (!$tk->gd): ?> disabled="disabled" <? endif ?>
                   <? if ($active == "gd"): ?> checked="checked" <? endif ?>
                   >
          </center>
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

      <tr>
        <td valign="top" style="width: 100px">
          <center>
            <input type="radio" name="graphics_toolkit" value="imagemagick"
                   <? if (!$tk->imagemagick): ?> disabled="disabled" <? endif ?>
                   <? if ($active == "imagemagick"): ?> checked="checked" <? endif ?>
                   >
          </center>
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

      <tr>
        <td valign="top" style="width: 100px">
          <center>
            <input type="radio" name="graphics_toolkit" value="graphicsmagick"
                   <? if (!$tk->graphicsmagick): ?> disabled="disabled" <? endif ?>
                   <? if ($active == "graphicsmagick"): ?> checked="checked" <? endif ?>
                   >
          </center>
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
    <input type="submit" value="<?= t("Save") ?>"/>
  </form>
</div>
