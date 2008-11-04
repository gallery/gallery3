<? defined("SYSPATH") or die("No direct script access."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Browse Photos :: <?= $item->title ?></title>

    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css" />
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/styles.css") ?>" media="screen,projection" />

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/container/assets/container.css" />
    <script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
    <script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/animation/animation-min.js"></script>
    <script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/container/container-min.js"></script>

    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/treeview/assets/skins/sam/treeview.css" />
    <script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/treeview/treeview-min.js"></script>
  </head>

  <body class="yui-skin-sam">
    <div id="doc2" class="yui-t5 gAlbumView">
      <div id="gHeader">
        <img id="gLogo" alt="<?= _("Logo") ?>" src="<?= $theme->url("images/logo.png") ?>" />

        <h1><?= $item->title ?></h1>

        <div id="gLoginMenu">
          <a href="#"><?= _("Register") ?></a> |
          <a href="#"><?= _("Login") ?>
          </div>

          <ul id="gSiteMenu">
            <li><a href="index.html"><?= _("HOME") ?></a></li>
            <li><a class="active" href="browse.html"><?= _("BROWSE") ?></a></li>
            <li><a href="upload.html"><?= _("UPLOAD") ?></a></li>
            <li><a href="upload.html"><?= _("MY GALLERY") ?></a></li>
            <li><a href="#"><?= _("ADMIN") ?></a></li>
          </ul>

          <ul id="gBreadcrumbs">
            <li class="root"><a href="#">Home</a></li>
            <li><a href="#">Friends &amp; Family</a></li>
            <li class="active"><span>Christmas 2007</span></li>
          </ul>

          <form id="gSearchForm">
            <input type="text" class="text" value="<?= _("Search Gallery ...") ?>"/>
            <input type="submit" class="submit" value="search" />
          </form>
        </div>
