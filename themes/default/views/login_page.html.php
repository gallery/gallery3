<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>
      <?= t("Please Login to Gallery") ?>
    </title>
    <link rel="shortcut icon" href="<?= $theme->url("images/favicon.ico") ?>" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="<?= url::file("lib/yui/reset-fonts-grids.css") ?>"
          media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?= url::file("lib/superfish/css/superfish.css") ?>"
          media="screen" />
    <link rel="stylesheet" type="text/css" href="<?= url::file("lib/themeroller/ui.base.css") ?>"
          media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/screen.css") ?>"
          media="screen,print,projection" />
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/fix-ie.css") ?>"
          media="screen,print,projection" />
    <![endif]-->
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery.form.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery-ui.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/gallery.dialog.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/superfish/js/superfish.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/jquery.scrollTo.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/jquery.localscroll.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
    <?= $theme->head() ?>
    <script>
      $("#gLoginLink").ready(function() {
        $("#gLoginLink").click();
      });
    </script>
 
  </head>

  <body>
    <?= $theme->page_top() ?>
    <div id="doc4" class="yui-t5 gView">
      <div id="hd">
        <?= $theme->site_status() ?>
        <div id="gHeader">
          <?= $theme->display("header.html") ?>
        </div>
      </div>
      <div id="bd">
        <div id="yui-main">
          <div class="yui-b">
            <div id="gContent" class="yui-g">
              <?= $theme->messages() ?>
            </div>
          </div>
        </div>
        <div id="gSidebar" class="yui-b">&nbsp;</div>
      </div>
      <div id="ft">
        <div id="gFooter">
          <?= $theme->display("footer.html") ?>
        </div>
      </div>
    </div>
    <?= $theme->page_bottom() ?>
  </body>
</html>
