<? defined("SYSPATH") or die("No direct script access."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>G3: Admin Dashboard</title>
    <link rel="stylesheet" href="<?= url::file("lib/yui/reset-fonts-grids.css") ?>"
        type="text/css" media="screen,projection">
    <link rel="stylesheet" href="<?= url::file("themes/default/css/screen.css") ?>"
        type="text/css" media="screen,projection">
    <link rel="stylesheet" href="<?= $theme->url("css/screen.css") ?>"
        type="text/css" media="screen,projection">
    <link rel="stylesheet" href="<?= $theme->url("css/superfish.css") ?>"
        type="text/css" media="screen,projection">
    <link rel="stylesheet" href="<?= $theme->url("css/superfish-navbar.css") ?>"
        type="text/css" media="screen,projection">
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery-ui.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/ui.core.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/ui.accordion.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/superfish.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
  </head>

  <body>
    <?= $theme->admin_page_top() ?>
    <div id="doc4" class="yui-t5 gView">
      <div id="hd">
        <div id="gHeader">
          <ul id="gLoginMenu">
            <li><?= html::anchor("albums/1", "Browse Gallery") ?></li>
            <li id="gLogoutLink"><a href="<?= url::site("logout?continue=albums/1") ?>">Logout</a></li>
          </ul>
          <img src="<?= $theme->url("images/logo.png") ?>" id="gLogo" alt="<?= _("Gallery 3: Your Photos on Your Web Site") ?>" />
          <div id="gSiteAdminMenu" class="gClearFix">
            <?= $theme->admin_menu() ?>
          </div>
        </div>
      </div>

      <?= $theme->messages() ?>

      <div id="bd">
        <div id="yui-main">
          <div class="yui-b">
            <div id="gContent" class="yui-g">
              <?= $content ?>
            </div>
          </div>
        </div>
        <div id="gSidebar" class="yui-b">

          <div id="gAvailableBlocks" class="gBlock">
            <form class="gBlockContent">
              <fieldset>
                <legend>Add Dashboard Blocks</legend>
                <label for="">Available blocks</label>
                <select name="" id="">
                  <option>Somthing</option>
                  <option>Somthing else</option>
                </select>
              </fieldset>
            </form>
          </div>

          <?= $theme->admin_sidebar_blocks() ?>

        </div>
      </div>
      <div id="ft">
        <div id="gFooter">
          <?= $theme->admin_footer(); ?>
          Footer
        </div>
      </div>
    </div>
    <?= $theme->admin_page_bottom() ?>
  </body>
</html>
