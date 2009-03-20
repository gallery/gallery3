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
    <link rel="stylesheet" type="text/css" href="<?= $theme->file("lib/yui/reset-fonts-grids.css") ?>"
          media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->file("lib/superfish/css/superfish.css") ?>"
          media="screen" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->file("lib/themeroller/ui.base.css") ?>"
          media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/screen.css") ?>"
          media="screen,print,projection" />
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/fix-ie.css") ?>"
          media="screen,print,projection" />
    <![endif]-->
    <script src="<?= $theme->file("lib/jquery.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->file("lib/jquery.form.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->file("lib/jquery-ui.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->file("lib/gallery.dialog.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->file("lib/superfish/js/superfish.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
    <script>
      $("#gLoginLink").ready(function() {
        $("#gLoginLink").click();
      });
    </script>
  </head>

  <body>
    <a id="gLoginLink" href="<?= url::site("login/ajax") ?>">Log in</a>
  </body>
</html>
