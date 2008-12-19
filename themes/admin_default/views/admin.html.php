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
    <script src="<?= url::file("lib/jquery-ui.packed.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/superfish.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
  </head>

  <body>
    <div id="doc4" class="yui-t5 gView">
      <div id="hd">
        <div id="gHeader">
          <ul id="gLoginMenu" class="gClearFix">
            <li><?= html::anchor("albums/1", "Browse Gallery") ?></li>
            <li id="gLogoutLink"><a href="<?= url::site("logout?continue=albums/1") ?>">Logout</a></li>
          </ul>
          <img src="<?= $theme->url("images/logo.png") ?>" id="gLogo" alt="<?= _("Gallery 3: Your Photos on Your Web Site") ?>" />
          <div id="gSiteAdminMenu">
            <?= $theme->admin_menu() ?>
          </div>
        </div>
      </div>
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

          <div id="gPlatform" class="gBlock">
            <h2>Gallery Stats</h2>
            <ul class="gBlockContent">
              <li>Version: 3.0</li>
              <li>Your gallery has 34 albums containing 603 photos with 26 comments.</li>
            </ul>
          </div>

          <div id="gPlatform" class="gBlock">
            <h2>Platform Information</h2>
            <ul class="gBlockContent">
              <li>Platform
                <ul>
                  <li>Apache 2.0.24
                    <ul>
                      <li>mod_rewrite: active</li>
                    </ul>
                  </li>
                  <li>PHP 5.2.8
                    <ul>
                      <li>Memory: 32MB</li>
                      <li></li>
                      <li></li>
                    </ul>
                  </li>
                  <li>MySQL 5.0.1</li>
                  <li>Graphics Toolkits
                    <ul>
                      <li>ImageMagick 1.6</li>
                      <li>GD</li>
                      <li>FFMPEG</li>
                    </ul>
                  </li>
                </ul>
              </li>
            </ul>
            <p class="gWarning">^ Display as a tree widget</p>
          </div>

          <div id="gProjectNews" class="gBlock">
            <h2>Gallery Project News</h2>
            <ul class="gBlockContent">
              <li>10-Apr <a href="#">Gallery 3.1 released!</a></li>
              <li>26-Feb <a href="#">New theme tutorials now available</a></li>
              <li>4-Feb <a href="#">Gallery 3.0 released!</a></li>
            </ul>
          </div>

        </div>
      </div>
      <div id="ft">
        <div id="gFooter">
          Footer
        </div>
      </div>
    </div>

  </body>
</html>
