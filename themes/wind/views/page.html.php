<?php defined("SYSPATH") or die("No direct script access.") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo  $theme->html_attributes() ?> xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <?php $theme->start_combining("script,css") ?>
    <title>
      <?php if ($page_title): ?>
        <?php echo  $page_title ?>
      <?php else: ?>
        <?php if ($theme->item()): ?>
          <?php echo  html::purify($theme->item()->title) ?>
        <?php elseif ($theme->tag()): ?>
          <?php echo  t("Photos tagged with %tag_title", array("tag_title" => $theme->tag()->name)) ?>
        <?php else: /* Not an item, not a tag, no page_title specified.  Help! */ ?>
          <?php echo  html::purify(item::root()->title) ?>
        <?php endif ?>
      <?php endif ?>
    </title>
    <link rel="shortcut icon"
          href="<?php echo  url::file(module::get_var("gallery", "favicon_url")) ?>"
          type="image/x-icon" />
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo  url::file(module::get_var("gallery", "apple_touch_icon_url")) ?>" />
    <?php if ($theme->page_type == "collection"): ?>
      <?php if (($thumb_proportion = $theme->thumb_proportion($theme->item(), 100, "width")) != 1): ?>
        <?php $new_width = round($thumb_proportion * 213) ?>
        <?php $new_height = round($thumb_proportion * 240) ?>
        <style type="text/css">
        .g-view #g-content #g-album-grid .g-item {
          width: <?php echo  $new_width ?>px;
          height: <?php echo  $new_height ?>px;
          /* <?php echo  $thumb_proportion ?> */
        }
        </style>
      <?php endif ?>
    <?php endif ?>

    <?php echo  $theme->script("json2-min.js") ?>
    <?php echo  $theme->script("jquery.js") ?>
    <?php echo  $theme->script("jquery.form.js") ?>
    <?php echo  $theme->script("jquery-ui.js") ?>
    <?php echo  $theme->script("gallery.common.js") ?>
    <?php /* MSG_CANCEL is required by gallery.dialog.js */ ?>
    <script type="text/javascript">
    var MSG_CANCEL = <?php echo  t('Cancel')->for_js() ?>;
    </script>
    <?php echo  $theme->script("gallery.ajax.js") ?>
    <?php echo  $theme->script("gallery.dialog.js") ?>
    <?php echo  $theme->script("superfish/js/superfish.js") ?>
    <?php echo  $theme->script("jquery.localscroll.js") ?>
    <?php echo  $theme->script("jquery.scrollTo.js") ?>
    <?php echo  $theme->script("gallery.show_full_size.js") ?>

    <?php echo  $theme->head() ?>

    <?php /* Theme specific CSS/JS goes last so that it can override module CSS/JS */ ?>
    <?php echo  $theme->script("ui.init.js") ?>
    <?php echo  $theme->css("yui/reset-fonts-grids.css") ?>
    <?php echo  $theme->css("superfish/css/superfish.css") ?>
    <?php echo  $theme->css("themeroller/ui.base.css") ?>
    <?php echo  $theme->css("screen.css") ?>
    <?php if (locales::is_rtl()): ?>
    <?php echo  $theme->css("screen-rtl.css") ?>
    <?php endif; ?>
    <!--[if lte IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo  $theme->url("css/fix-ie.css") ?>"
          media="screen,print,projection" />
    <![endif]-->

    <?php echo  $theme->get_combined("css") ?>
    <?php echo  $theme->get_combined("script") ?>
  </head>

  <body <?php echo  $theme->body_attributes() ?>>
    <?php echo  $theme->page_top() ?>
    <div id="doc4" class="yui-t5 g-view">
      <?php echo  $theme->site_status() ?>
      <div id="g-header" class="ui-helper-clearfix">
        <div id="g-banner">
          <?php if ($header_text = module::get_var("gallery", "header_text")): ?>
          <?php echo  $header_text ?>
          <?php else: ?>
          <a id="g-logo" class="g-left" href="<?php echo  item::root()->url() ?>" title="<?php echo  t("go back to the Gallery home")->for_html_attr() ?>">
            <img width="107" height="48" alt="<?php echo  t("Gallery logo: Your photos on your web site")->for_html_attr() ?>" src="<?php echo  url::file("lib/images/logo.png") ?>" />
          </a>
          <?php endif ?>
          <?php echo  $theme->user_menu() ?>
          <?php echo  $theme->header_top() ?>

          <!-- hide the menu until after the page has loaded, to minimize menu flicker -->
          <div id="g-site-menu" style="visibility: hidden">
            <?php echo  $theme->site_menu($theme->item() ? "#g-item-id-{$theme->item()->id}" : "") ?>
          </div>
          <script type="text/javascript"> $(document).ready(function() { $("#g-site-menu").css("visibility", "visible"); }) </script>

          <?php echo  $theme->header_bottom() ?>
        </div>

        <?php if (!empty($breadcrumbs)): ?>
        <ul class="g-breadcrumbs">
          <?php foreach ($breadcrumbs as $breadcrumb): ?>
           <li class="<?php echo  $breadcrumb->last ? "g-active" : "" ?>
                      <?php echo  $breadcrumb->first ? "g-first" : "" ?>">
            <?php if (!$breadcrumb->last): ?> <a href="<?php echo  $breadcrumb->url ?>"><?php endif ?>
            <?php echo  html::clean(text::limit_chars($breadcrumb->title, module::get_var("gallery", "visible_title_length"))) ?>
            <?php if (!$breadcrumb->last): ?></a><?php endif ?>
           </li>
          <?php endforeach ?>
        </ul>
        <?php endif ?>
      </div>
      <div id="bd">
        <div id="yui-main">
          <div class="yui-b">
            <div id="g-content" class="yui-g">
              <?php echo  $theme->messages() ?>
              <?php echo  $content ?>
            </div>
          </div>
        </div>
        <div id="g-sidebar" class="yui-b">
          <?php if (!in_array($theme->page_subtype, array("login", "error"))): ?>
          <?php echo  new View("sidebar.html") ?>
          <?php endif ?>
        </div>
      </div>
      <div id="g-footer" class="ui-helper-clearfix">
        <?php echo  $theme->footer() ?>
        <?php if ($footer_text = module::get_var("gallery", "footer_text")): ?>
        <?php echo  $footer_text ?>
        <?php endif ?>

        <?php if (module::get_var("gallery", "show_credits")): ?>
        <ul id="g-credits" class="g-inline">
          <?php echo  $theme->credits() ?>
        </ul>
        <?php endif ?>
      </div>
    </div>
    <?php echo  $theme->page_bottom() ?>
  </body>
</html>
