<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gGraphics">
  <h1> <?= _("Graphics Settings") ?> </h1>
  <p>
    <?= _("Gallery needs a graphics toolkit in order to manipulate your photos.  Please choose one from the list below.") ?>
  </p>

  <form method="post" action="<?= url::site("admin/graphics/save") ?>">
    <?= access::csrf_form_field() ?>
    <h2> <?= _("Graphics Toolkits") ?> </h2>
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
          <h3> <?= _("GD") ?> </h3>
          <p>
            <? printf(_("The GD graphics library is an extension to PHP commonly installed most webservers.  Please refer to the %sGD website%s for more information."), "<a href=\"http://www.boutell.com/gd/\">", "</a>") ?>
          </p>
          <? if ($tk->gd): ?>
          <p class="gSuccess">
            <? printf(_("You have GD version %s."), $tk->gd) ?>
          </p>
          <? else: ?>
          <p class="gInfo">
            <?= _("You do not have GD installed.") ?>
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
          <h3> <?= _("ImageMagick") ?> </h3>
          <p>
            <? printf(_("ImageMagick is a standalone graphics program available on most Linux systems.  Please refer to the %sImageMagick website%s for more information."), "<a href=\"http://www.imagemagick.org/\">", "</a>") ?>
          </p>
          <? if ($tk->imagemagick): ?>
          <p class="gSuccess">
            <? printf(_("You have ImageMagick installed in %s"), $tk->imagemagick) ?>
          </p>
          <? else: ?>
          <p class="gInfo">
            <?= _("ImageMagick is not available on your system.") ?>
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
          <h3> <?= _("GraphicsMagick") ?> </h3>
          <p>
            <? printf(_("GraphicsMagick is a standalone graphics program available on most Linux systems.  Please refer to the %sGraphicsMagick website%s for more information."), "<a href=\"http://www.graphicsmagick.org/\">", "</a>") ?>
          </p>
          <? if ($tk->graphicsmagick): ?>
          <p class="gSuccess">
            <? printf(_("You have GraphicsMagick installed in %s"), $tk->graphicsmagick) ?>
          </p>
          <? else: ?>
          <p class="gInfo">
            <?= _("GraphicsMagick is not available on your system.") ?>
          </p>
          <? endif ?>
        </td>
      </tr>
    </table>
    <input type="submit" value="<?= _("Save") ?>"/>
  </form>
</div>
