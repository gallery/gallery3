<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo $theme->html_attributes() ?> xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <?php $theme->start_combining("script,css") ?>
    <title>
      <?php if ($page_title): ?>
        <?php echo t("Gallery Admin: %page_title", array("page_title" => $page_title)) ?>
      <?php else: ?>
        <?php echo t("Admin dashboard") ?>
      <?php endif ?>
    </title>
    <link rel="shortcut icon"
          href="<?php echo url::file(module::get_var("gallery", "favicon_url")) ?>"
          type="image/x-icon" />
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo url::file(module::get_var("gallery", "apple_touch_icon_url")) ?>" />

    <?php echo $theme->script("jquery.js") ?>
    <?php echo $theme->script("jquery.form.js") ?>
    <?php echo $theme->script("jquery-ui.js") ?>
    <?php echo $theme->script("gallery.common.js") ?>
    <?php /* MSG_CANCEL is required by gallery.dialog.js */ ?>
    <script type="text/javascript">
    var MSG_CANCEL = <?php echo t("Cancel")->for_js() ?>;
    </script>
    <?php echo $theme->script("gallery.ajax.js") ?>
    <?php echo $theme->script("gallery.dialog.js") ?>
    <?php echo $theme->script("superfish/js/superfish.js") ?>
    <?php echo $theme->script("jquery.scrollTo.js") ?>

    <?php echo $theme->admin_head() ?>

    <?php /* Theme specific CSS/JS goes last so that it can override module CSS/JS */ ?>
    <?php echo $theme->script("ui.init.js") ?>
    <?php echo $theme->css("yui/reset-fonts-grids.css") ?>
    <?php echo $theme->css("themeroller/ui.base.css") ?>
    <?php echo $theme->css("superfish/css/superfish.css") ?>
    <?php echo $theme->css("screen.css") ?>
    <?php if (locales::is_rtl()): ?>
    <?php echo $theme->css("screen-rtl.css") ?>
    <?php endif; ?>
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo $theme->url("css/fix-ie.css") ?>"
          media="screen,print,projection" />
    <![endif]-->

    <?php echo $theme->get_combined("css") ?>
    <?php echo $theme->get_combined("script") ?>
  </head>

  <body <?php echo $theme->body_attributes() ?>>
    <?php echo $theme->admin_page_top() ?>
    <?php if ($sidebar): ?>
    <div id="doc3" class="yui-t5 g-view">
    <?php else: ?>
    <div id="doc3" class="yui-t7 g-view">
    <?php endif; ?>
      <?php echo $theme->site_status() ?>
      <div id="g-header" class="ui-helper-clearfix">
        <?php echo $theme->admin_header_top() ?>
        <a id="g-logo" class="g-left" href="<?php echo item::root()->url() ?>" title="<?php echo t("go back to the Gallery")->for_html_attr() ?>">
          &larr; <?php echo t("back to the ...") ?>
        </a>
        <?php echo $theme->user_menu() ?>
        <!-- hide the menu until after the page has loaded, to minimize menu flicker -->
        <div id="g-site-admin-menu" class="ui-helper-clearfix" style="visibility: hidden">
          <?php echo $theme->admin_menu() ?>
        </div>
        <script type="text/javascript"> $(document).ready(function() { $("#g-site-admin-menu").css("visibility", "visible"); }) </script>
        <?php echo $theme->admin_header_bottom() ?>
      </div>
      <div id="bd">
        <div id="yui-main">
          <div class="yui-b">
            <div id="g-content" class="yui-g">
              <?php echo $theme->messages() ?>
              <?php echo $content ?>
            </div>
          </div>
        </div>
        <?php if ($sidebar): ?>
        <div id="g-sidebar" class="yui-b">
          <?php echo $sidebar ?>
        </div>
        <?php endif ?>
      </div>
      <div id="g-footer" class="g-inline ui-helper-clearfix">
        <?php echo $theme->admin_footer() ?>
        <?php if (module::get_var("gallery", "show_credits")): ?>
        <ul id="g-credits" class="g-inline">
          <?php echo $theme->admin_credits() ?>
        </ul>
        <?php endif ?>
      </div>
    </div>
    <?php echo $theme->admin_page_bottom() ?>
  </body>
</html>
