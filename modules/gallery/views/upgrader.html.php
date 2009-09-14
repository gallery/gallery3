<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title><?= t("Gallery 3 Upgrader") ?></title>
    <link rel="stylesheet" type="text/css" href="<?= url::file("modules/gallery/css/upgrader.css") ?>"
          media="screen,print,projection" />
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
  </head>
  <body>
    <div id="outer">
      <img src="<?= url::file("modules/gallery/images/gallery.png") ?>" />
      <div id="inner">
        <? if ($can_upgrade): ?>
        <? if ($done): ?>
        <div id="confirmation">
          <a onclick="$('#confirmation').slideUp(); return false;" href="#" class="close">[x]</a>
          <div>
            <h1> <?= t("That's it!") ?> </h1>
            <p>
              <?= t("Your <a href=\"%url\">Gallery</a> is up to date.",
                    array("url" => html::mark_clean(item::root()->url()))) ?>
            </p>
          </div>
        </div>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#confirmation").css("left", Math.round(($(window).width() - $("#confirmation").width()) / 2));
            $("#confirmation").css("top", Math.round(($(window).height() - $("#confirmation").height()) / 2));
          });
        </script>
        <? endif ?>
        <p class="gray_on_done">
          <?= t("Welcome to the Gallery upgrader.  One click and you're done!") ?>
        </p>
        <table>
          <tr class="gray_on_done">
            <th> <?= t("Module name") ?> </th>
            <th> <?= t("Installed version") ?> </th>
            <th> <?= t("Available version") ?> </th>
          </tr>

          <? foreach ($available as $id => $module): ?>
          <? if ($module->active): ?>
          <tr class="<?= $module->version == $module->code_version ? "current" : "upgradeable" ?>" >
            <td class="name <?= $id ?>">
              <?= t($module->name) ?>
            </td>
            <td>
              <?= $module->version ?>
            </td>
            <td>
              <?= $module->code_version ?>
            </td>
          </tr>
          <? else: ?>
          <? @$inactive++ ?>
          <? endif ?>
          <? endforeach ?>
        </table>

        <div class="button gray_on_done">
          <a href="<?= url::site("upgrader/upgrade") ?>">
            <?= t("Upgrade all") ?>
          </a>
        </div>

        <? if (@$inactive): ?>
        <p class="gray_on_done">
          <?= t("The following modules are inactive and don't require an upgrade.") ?>
        </p>
        <ul class="gray_on_done">
          <? foreach ($available as $module): ?>
          <? if (!$module->active): ?>
          <li>
            <?= t($module->name) ?>
          </li>
          <? endif ?>
          <? endforeach ?>
        </p>
        <? endif ?>
        <? else: // can_upgrade ?>
        <h1> <?= t("Who are you?") ?> </h1>
        <p>
          <?= t("You're not logged in as an administrator, so we have to verify you to make sure it's ok for you to do an upgrade.  To prove you can run an upgrade, create a file called <br/><b>%name</b> in your <b>gallery3/var/tmp</b> directory.", array("name" => "$upgrade_token")) ?>
        </p>
        <a href="<?= url::site("upgrader?") ?>"><?= t("Ok, I've done that") ?></a>
        <? endif // can_upgrade ?>
      </div>
      <div id="footer">
        <p>
          <i>
            <?= t("Did something go wrong? Try the <a href=\"%faq_url\">FAQ</a> or ask in the <a href=\"%forums_url\">Gallery forums</a>.",
                array("faq_url" => "http://codex.gallery2.org/Gallery3:FAQ",
                      "forums_url" => "http://gallery.menalto.com/forum")) ?>
          </i>
        </p>
      </div>
    </div>
  </body>
</html>
