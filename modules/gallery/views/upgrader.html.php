<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title><?php echo  t("Gallery 3 upgrader") ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo  url::file("modules/gallery/css/upgrader.css") ?>"
          media="screen,print,projection" />
    <script src="<?php echo  url::file("lib/jquery.js") ?>" type="text/javascript"></script>
  </head>
  <body<?php if (locales::is_rtl()) { echo ' class="rtl"'; } ?>>
    <div id="outer">
      <img id="logo" src="<?php echo  url::file("modules/gallery/images/gallery.png") ?>" />
      <div id="inner">
        <?php if ($can_upgrade): ?>
        <div id="dialog" style="visibility: hidden">
          <a id="dialog_close_link" style="display: none" onclick="$('#dialog').fadeOut(); return false;" href="#" class="close">[x]</a>
          <div id="busy" style="display: none">
            <h1>
              <img width="16" height="16" src="<?php echo  url::file("modules/gallery/images/loading-small.gif") ?>"/>
              <?php echo  t("Upgrade in progress!") ?>
            </h1>
            <p>
              <?php echo  t("Please don't refresh or leave the page.") ?>
            </p>
          </div>
          <div id="done" style="display: none">
            <h1> <?php echo  t("That's it!") ?> </h1>
            <p>
              <?php echo  t("Your Gallery is up to date.<br/><a href=\"%url\">Return to your Gallery</a>",
                    array("url" => html::mark_clean(url::base()))) ?>
            </p>
          </div>
          <div id="failed" style="display: none">
            <h1> <?php echo  t("Some modules failed to upgrade!") ?> </h1>
            <p>
              <?php echo  t("Failed modules are <span class=\"failed\">highlighted</span>.  Try getting newer versions or <a href=\"%admin_modules\">deactivating those modules</a>.", array("admin_modules" => url::site("admin/modules"))) ?>
            </p>
          </div>
        </div>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#dialog").css("left", Math.round(($(window).width() - $("#dialog").width()) / 2));
            $("#dialog").css("top", Math.round(($(window).height() - $("#dialog").height()) / 2));
            $("#upgrade_link").click(function(event) { show_busy() });

            <?php if ($done): ?>
            show_done();
            <?php endif ?>

            <?php if ($failed): ?>
            show_failed();
            <?php endif ?>
          });

          var show_busy = function() {
            $("#dialog").css("visibility", "visible");
            $("#busy").show();
            $("#upgrade_link").parent().removeClass("button-active");
            $("#upgrade_link").replaceWith($("#upgrade_link").html())
          }

          var show_done = function() {
            $("#dialog").css("visibility", "visible");
            $("#done").show();
            $("#dialog_close_link").show();
          }

          var show_failed = function() {
            $("#dialog").css("visibility", "visible");
            $("#failed").show();
            $("#dialog_close_link").show();
          }
        </script>
        <div id="welcome_message">
          <p class="<?php echo  $done ? "muted" : "" ?>">
            <?php echo  t("Welcome to the Gallery upgrader.  One click and you're done!") ?>
          </p>
        </div>

        <?php if ($done): ?>
        <div id="upgrade_button" class="button muted">
          <?php echo  t("Upgrade all") ?>
        </div>
        <?php else: ?>
        <div id="upgrade_button" class="button button-active">
          <a id="upgrade_link" href="<?php echo  url::site("upgrader/upgrade?csrf=" . access::csrf_token()) ?>">
            <?php echo  t("Upgrade all") ?>
          </a>
        </div>
        <?php endif ?>

        <?php if ($obsolete_modules_message): ?>
        <div id="obsolete_modules_message">
          <p>
            <span class="failed"><?php echo  t("Warning!") ?></span>
            <?php echo  $obsolete_modules_message ?>
          </p>
        </div>
        <?php endif ?>

        <table>
          <tr class="<?php echo  $done ? "muted" : "" ?>">
            <th class="name"> <?php echo  t("Module name") ?> </th>
            <th> <?php echo  t("Installed version") ?> </th>
            <th> <?php echo  t("Available version") ?> </th>
          </tr>

          <?php foreach ($available as $id => $module): ?>
          <?php if ($module->active): ?>
          <tr class="<?php echo  $module->version == $module->code_version ? "current" : "upgradeable" ?> <?php echo  in_array($id, $failed) ? "failed" : "" ?>" >
            <td class="name <?php echo  $id ?>">
              <?php echo  t($module->name) ?>
            </td>
            <td>
              <?php echo  $module->version ?>
            </td>
            <td>
              <?php echo  $module->code_version ?>
            </td>
          </tr>
          <?php else: ?>
          <?php @$inactive++ ?>
          <?php endif ?>
          <?php endforeach ?>
        </table>

        <?php if (@$inactive): ?>
        <p class="<?php echo  $done ? "muted" : "" ?>">
          <?php echo  t("The following modules are inactive and don't require an upgrade.") ?>
        </p>
        <ul class="<?php echo  $done ? "muted" : "" ?>">
          <?php foreach ($available as $module): ?>
          <?php if (!$module->active): ?>
          <li>
            <?php echo  t($module->name) ?>
          </li>
          <?php endif ?>
          <?php endforeach ?>
        </ul>
        <?php endif ?>
        <?php else: // can_upgrade ?>
        <h1> <?php echo  t("Who are you?") ?> </h1>
        <p>
          <?php echo  t("You're not logged in as an administrator, so we have to verify you to make sure it's ok for you to do an upgrade.  To prove you can run an upgrade, create a file called <b> %name </b> in your <b>%tmp_dir_path</b> directory.",
                array("name" => "$upgrade_token",
                      "tmp_dir_path" => "gallery3/var/tmp")) ?>
        </p>
        <a href="<?php echo  url::site("upgrader?") ?>"><?php echo  t("Ok, I've done that") ?></a>
        <?php endif // can_upgrade ?>
      </div>
      <div id="footer">
        <p>
          <em>
            <?php echo  t("Did something go wrong? Try the <a href=\"%faq_url\">FAQ</a> or ask in the <a href=\"%forums_url\">Gallery forums</a>.",
                array("faq_url" => "http://codex.galleryproject.org/Gallery3:FAQ",
                      "forums_url" => "http://galleryproject.org/forum")) ?>
          </em>
        </p>
      </div>
    </div>
  </body>
</html>
