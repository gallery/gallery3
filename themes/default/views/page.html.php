<? defined("SYSPATH") or die("No direct script access."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Tranisitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>
      <? if ($page_type == "tag"): ?>
      <?= _("Browse Tags") ?> :: <?= $tag->name ?>
      <? else: ?>
      <?= _("Browse Photos") ?> :: <?= $item->title ?>
      <? endif ?>
    </title>
    <link rel="stylesheet" type="text/css" href="<?= url::file("lib/yui/reset-fonts-grids.css") ?>"
          media="screen,print,projection" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/superfish.css") ?>"
          media="screen" />
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/screen.css") ?>"
          media="screen,print,projection" />
    <script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery.form.js") ?>" type="text/javascript"></script>
    <script src="<?= url::file("lib/jquery.listen.js") ?>" type="text/javascript"></script>
    <script src="<?= $theme->url("js/superfish.js") ?>" type="text/javascript"></script>
    <?= $theme->head() ?>
    <?= html::script("lib/jquery-ui.js") ?>
    <script src="<?= $theme->url("js/ui.init.js") ?>" type="text/javascript"></script>
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("css/fix-ie.css") ?>"
          media="screen,print,projection" />
    <![endif]-->
  </head>

  <body>
    <?= $theme->page_top() ?>
    <div id="doc4" class="yui-t5 gView">
      <div id="hd">
        <div id="gHeader">
          <?= $theme->display("header.html") ?>
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
          <?= $theme->display("sidebar.html") ?>
        </div>
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
