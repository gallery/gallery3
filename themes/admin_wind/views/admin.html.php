<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>
      <? if ($page_title): ?>
        <?= t("Gallery Admin: %page_title", array("page_title" => $page_title)) ?>
      <? else: ?>
        <?= t("Admin dashboard") ?>
      <? endif ?>
    </title>
    <link rel="shortcut icon" href="<?= url::file(module::get_var("gallery", "favicon_url")) ?>" type="image/x-icon" />

    <?= $theme->css("yui/reset-fonts-grids.css") ?>
    <?= $theme->css("themeroller/ui.base.css") ?>
    <?= $theme->css("superfish/css/superfish.css") ?>
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
   var MSG_CANCEL = <?= t("Cancel")->for_js() ?>;
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
      <div id="g-header" class="ui-helper-clearfix">
        <?= $theme->admin_header_top() ?>
        <a id="g-logo" class="g-left" href="<?= item::root()->url() ?>" title="<?= t("go back to the Gallery")->for_html_attr() ?>">
          &larr; <?= t("back to the ...") ?>
        </a>
        <?= $theme->user_menu() ?>
        <!-- hide the menu until after the page has loaded, to minimize menu flicker -->
        <div id="g-site-admin-menu" class="ui-helper-clearfix" style="visibility: hidden">
          <?= $theme->admin_menu() ?>
        </div>
        <script type="text/javascript"> $(document).ready(function() { $("#g-site-admin-menu").css("visibility", "visible"); }) </script>
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
        <? if (module::get_var("gallery", "show_credits")): ?>
        <ul id="g-credits" class="g-inline">
          <?= $theme->admin_credits() ?>
        </ul>
        <? endif ?>
      </div>
    </div>
    <?= $theme->admin_page_bottom() ?>
  </body>
</html>
