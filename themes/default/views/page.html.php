<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>
      <? if (empty($page_title)): ?>
        <?= t("Browse Photos") ?>
        <? if (!empty($item)): ?>
        :: <?= $item->title ?>
        <? endif ?>
      <? else: ?>
        <?= $page_title ?>
      <? endif ?>
      <?= $theme->page_type ?>
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
    <? if ($theme->page_type == 'album'): ?>
      <? if ($thumb_proportion != 1): ?>
        <? $new_width = $thumb_proportion * 213 ?>
        <? $new_height = $thumb_proportion * 240 ?>
    <style type="text/css">
    #gContent #gAlbumGrid .gItem {
      width: <?= $new_width ?>px;
      height: <?= $new_height ?>px;
      /* <?= $thumb_proportion ?> */
    }
    </style>
      <? endif ?>
    <? endif ?>
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery.form.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery-ui.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/gallery.common.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/gallery.dialog.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/gallery.form.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/superfish/js/superfish.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/jquery.scrollTo.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/jquery.localscroll.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
    <?= $theme->head() ?>
  </head>

  <body>
    <?= $theme->page_top() ?>
    <div id="doc4" class="yui-t5 gView">
      <?= $theme->site_status() ?>
      <div id="gHeader">
        <?= $theme->display("header.html") ?>
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
        <div id="gSidebar" class="yui-b">
          <?= $theme->display("sidebar.html") ?>
        </div>
      </div>
      <div id="gFooter">
        <?= $theme->display("footer.html") ?>
      </div>
    </div>
    <?= $theme->page_bottom() ?>
  </body>
</html>
