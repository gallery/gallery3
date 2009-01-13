<div id="graphicsmagick" class="gBlock <?= $tk->graphicsmagick ? "" : "unavailable" ?>">
  <h3> <?= t("GraphicsMagick") ?> </h3>
  <img class="logo" width="107" height="76" src="<?= url::file("core/images/graphicsmagick.png"); ?>" alt="<? t("Visit the GraphicsMagick project site") ?>" />
  <p>
    <?= t("GraphicsMagick is a standalone graphics program available on most Linux systems.  Please refer to the <a href=\"{{url}}\">GraphicsMagick website</a> for more information.",
        array("url" => "http://www.graphicsmagick.org")) ?>
  </p>
  <? if ($tk->graphicsmagick): ?>
  <p class="gSuccess">
    <?= t("GraphicsMagick is available in {{path}}", array("path" => $tk->graphicsmagick)) ?>
  </p>
  <? else: ?>
  <p class="gInfo">
    <?= t("GraphicsMagick is not available on your system.") ?>
  </p>
  <? endif ?>
</div>
