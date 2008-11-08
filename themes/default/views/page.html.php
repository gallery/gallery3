<? defined("SYSPATH") or die("No direct script access."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?= _("Browse Photos") ?> :: <?= $item->title ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?= url::base() .
          "lib/yui/reset-fonts-grids.css" ?>" media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?= url::base() .
          "lib/yui/base-min.css" ?>" media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?=
      $theme->url("css/screen.css") ?>" media="screen,print,projection" />
    <script src="<?= url::base() . "lib/jquery.js" ?>" type="text/javascript"></script>
    <script src="<?= url::base() . "lib/jquery.jeditable.js" ?>" type="text/javascript"></script>
  </head>

  <body>
    <div id="doc2" class="yui-t5 gView">
      <?= $theme->display("header.html") ?>
      <div id="bd">
        <div id="yui-main">
          <div id="gContent" class="yui-b">
            <?= $content ?>
          </div>
        </div>
        <?= $theme->display("sidebar.html") ?>
      </div>
      <?= $theme->display("footer.html") ?>
    </div>
    <?= $theme->in_place_edit(); ?>
  </body>
</html>
