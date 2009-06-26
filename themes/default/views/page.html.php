<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>
      <? if ($page_title): ?>
        <?= $page_title ?>
      <? else: ?>
        <? if ($theme->item()): ?>
          <? if ($theme->item()->is_album()): ?>
          <?= t("Browse Album :: %album_title", array("album_title" => p::clean($theme->item()->title))) ?>
          <? elseif ($theme->item()->is_photo()): ?>
          <?= t("Photo :: %photo_title", array("photo_title" => p::clean($theme->item()->title))) ?>
          <? else: ?>
          <?= t("Movie :: %movie_title", array("movie_title" => p::clean($theme->item()->title))) ?>
          <? endif ?>
        <? elseif ($theme->tag()): ?>
          <?= t("Browse Tag :: %tag_title", array("tag_title" => p::clean($theme->tag()->name))) ?>
        <? else: /* Not an item, not a tag, no page_title specified.  Help! */ ?>
          <?= t("Gallery") ?>
        <? endif ?>
      <? endif ?>
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
    <!--[if lt IE 8]>
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
    <?= $theme->head() ?>
  </head>

  <body <?= $theme->body_attributes() ?>>
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
          <? if ($theme->page_type != "login"): ?>
          <?= $theme->display("sidebar.html") ?>
          <? endif ?>
        </div>
      </div>
      <div id="gFooter">
        <?= $theme->display("footer.html") ?>
      </div>
    </div>
    <?= $theme->page_bottom() ?>
  </body>
</html>
