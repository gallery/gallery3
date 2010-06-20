/**
 * Initialize jQuery UI and Gallery Plugins
 * @todo Move ui-corner-all assignments to theme admin views
 */

$(document).ready(function(){

  // Initialize Superfish menus
  $("#g-site-admin-menu .g-menu").hide().addClass("sf-menu");
  $("#g-site-admin-menu .g-menu").superfish({
    delay: 500,
    animation: {
      opacity: "show",
      height: "show"
    },
    pathClass: "g-selected",
    speed: "fast"
  }).show();

  // Initialize status message effects
  $("#g-action-status li").gallery_show_message();

  // Initialize modal dialogs
  $(".g-dialog-link").gallery_dialog();

  // Initialize short forms
  $(".g-short-form").gallery_short_form();

  // Initialize ajax links
  $(".g-ajax-link").gallery_ajax();

  // Initialize panels
  $(".g-panel-link").gallery_panel();

  if ($("#g-photo-stream").length) {
    // Vertically align thumbs in photostream
    $(".g-item").gallery_valign();
  }

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]:not(.g-short-form input)").addClass("ui-state-default ui-corner-all");

  // Round view menu buttons
  if ($("#g-admin-comments-menu").length) {
    $("#g-admin-comments-menu ul").removeClass("g-menu");
    $("#g-admin-comments-menu").addClass("g-buttonset");
    $("#g-admin-comments-menu a").addClass("g-button ui-state-default");
    $("#g-admin-comments-menu ul li:first a").addClass("ui-corner-left");
    $("#g-admin-comments-menu ul li:last a").addClass("ui-corner-right");
  }

  // Round corners
  $(".g-selected").addClass("ui-corner-all");
  $(".g-available .g-block").addClass("ui-corner-all");
  $(".g-unavailable").addClass("ui-corner-all");

  // Remove titles for menu options since we're displaying that text anyway
  $(".sf-menu a, .sf-menu li").removeAttr("title");

  // Initialize button hover effect
  $.fn.gallery_hover_init();
});
