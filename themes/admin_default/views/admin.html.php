<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>G3: Admin Dashboard</title>
    <link rel="shortcut icon" href="<?= url::file("themes/default/images/favicon.ico") ?>" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="<?= url::file("lib/yui/reset-fonts-grids.css") ?>"
        media="screen,projection" />
    <link rel="stylesheet" type="text/css" type="text/css" href="<?= url::file("lib/themeroller/ui.base.css") ?>"
        media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="<?= url::file("lib/superfish/css/superfish.css") ?>"
        media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="<?= url::file("themes/default/css/screen.css") ?>"
        media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/screen.css") ?>"
        media="screen,projection" />
   <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/fix-ie.css") ?>"
        media="screen,print,projection" />
   <![endif]-->
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery.form.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery-ui.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/gallery.dialog.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/superfish/js/superfish.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/jquery.dropshadow.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
    <?= $theme->admin_head() ?>
  </head>

  <body>
    <?= $theme->admin_page_top() ?>
    <? if ($sidebar): ?>
    <div id="doc3" class="yui-t5 gView">
    <? else: ?>
    <div id="doc3" class="yui-t7 gView">
    <? endif; ?>
      <?= $theme->site_status() ?>
      <div id="gHeader">
        <?= $theme->admin_header_top() ?>
        <ul id="gLoginMenu">
          <li class="first"><?= html::anchor("albums/1", "Browse the Gallery") ?></li>
          <li id="gLogoutLink"><a href="<?= url::site("logout?continue=albums/1") ?>">Logout</a></li>
        </ul>
        <a href="<?= url::site("albums/1") ?>"><img src="<?= url::file("themes/default/images/logo.png") ?>" id="gLogo" alt="<?= t("Gallery 3: Your Photos on Your Web Site") ?>" /></a>
        <div id="gSiteAdminMenu" style="display: none">
          <?= $theme->admin_menu() ?>
        </div>
        <?= $theme->admin_header_bottom() ?>
      </div>
      <div id="bd">
        <div id="yui-main">
          <div class="yui-b">
            <div id="gContent" class="yui-g">
              <?= $theme->messages() ?>
              <?= $content ?>
            </div>
          </div>
        </div>
        <? if ($sidebar): ?>
        <div id="gSidebar" class="yui-b">
          <?= $sidebar ?>
        </div>
        <? endif ?>
      </div>
      <div id="gFooter">
        <?= $theme->admin_footer() ?>
        <div>
          <?= $theme->admin_credits() ?>
        </div>
      </div>
    </div>
    <?= $theme->admin_page_bottom() ?>
  </body>
</html>
