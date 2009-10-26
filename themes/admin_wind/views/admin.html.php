<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title><?= t("Admin Dashboard") ?></title>
    <link rel="shortcut icon" href="<?= url::file("lib/images/favicon.ico") ?>" type="image/x-icon" />

    <?= $theme->css("yui/reset-fonts-grids.css") ?>
    <?= $theme->css("themeroller/ui.base.css") ?>
    <?= $theme->css("superfish/css/superfish.css") ?>
    <?= $theme->css("gallery.common.css") ?>
    <?= $theme->css("screen.css") ?>
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?= $theme->url("fix-ie.css") ?>"
          media="screen,print,projection" />
    <![endif]-->

   <?= $theme->script("jquery.js") ?>
   <?= $theme->script("jquery.form.js") ?>
   <?= $theme->script("jquery-ui.js") ?>
   <?= $theme->script("gallery.common.js") ?>
   <? /* MSG_CANCEL is required by gallery.dialog.js */ ?>
   <script type="text/javascript">
   var MSG_CANCEL = <?= t('Cancel')->for_js() ?>;
   </script>
   <?= $theme->script("gallery.ajax.js") ?>
   <?= $theme->script("gallery.dialog.js") ?>
   <?= $theme->script("superfish/js/superfish.js") ?>
   <?= $theme->script("ui.init.js") ?>

   <?= $theme->admin_head() ?>
  </head>

  <body <?= $theme->body_attributes() ?>>
    <?= $theme->admin_page_top() ?>
    <? if ($sidebar): ?>
    <div id="doc3" class="yui-t5 g-view">
    <? else: ?>
    <div id="doc3" class="yui-t7 g-view">
    <? endif; ?>
      <?= $theme->site_status() ?>
      <div id="g-header">
        <?= $theme->admin_header_top() ?>
        <ul id="g-login-menu" class="g-inline">
          <li class="first"><?= html::anchor(item::root()->abs_url(), "&larr; ".t("Back to the Gallery")) ?></li>
          <li id="g-logout-link"><a href="<?= url::site("logout?csrf=$csrf&amp;continue=" . urlencode(item::root()->url())) ?>"><?= t("Logout") ?></a></li>
        </ul>
        <a id="g-logo" href="<?= item::root()->url() ?>" title="<?= t("go back to the Gallery")->for_html_attr() ?>">
          &larr; <?= t("back to the ...") ?>
        </a>
        <div id="g-site-admin-menu" class="ui-helper-clearfix">
          <?= $theme->admin_menu() ?>
        </div>
        <?= $theme->admin_header_bottom() ?>
      </div>
      <div id="bd">
        <div id="yui-main">
          <div class="yui-b">
            <div id="g-content" class="yui-g">
              <?= $theme->messages() ?>
              <?= $content ?>
            </div>
          </div>
        </div>
        <? if ($sidebar): ?>
        <div id="g-sidebar" class="yui-b">
          <?= $sidebar ?>
        </div>
        <? endif ?>
      </div>
      <div id="g-footer" class="g-inline ui-helper-clearfix">
        <?= $theme->admin_footer() ?>
        <div>
          <?= $theme->admin_credits() ?>
        </div>
      </div>
    </div>
    <?= $theme->admin_page_bottom() ?>
  </body>
</html>
