/**
 * Initialize jQuery UI and Plugin elements
 *
 * @todo Standardize how elements requiring listeners are handled
 *        http://docs.jquery.com/Events/live
 */

var shortForms = new Array(
  "#gQuickSearchForm",
  "#gAddTagForm",
  "#gSearchForm"
);

$(document).ready(function() {

  // Remove .gMenu from thumb menu's before initializing Superfish
  // @todo gallery_menu should only apply gMenu to top-level menus, submenus should be gSubMenu-N

  // Initialize Superfish menus
  $("ul.gMenu").addClass("sf-menu");
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    speed: 'fast'
  });
  $("#gSiteMenu").css("display", "block");

  // Initialize status message effects
  $("#gMessage li").gallery_show_message();

  // Initialize dialogs
  $("#gLoginLink").addClass("gDialogLink");
  $(".gDialogLink").gallery_dialog();

  // Initialize view menu
  if ($("#gViewMenu").length) {
    $("#gViewMenu ul").removeClass("gMenu").removeClass("sf-menu");
    $("#gViewMenu a").addClass("ui-icon");
  }

  // Initialize short forms
  handleShortFormEvent(shortForms);
  $(".gShortForm input[type=text]").addClass("ui-corner-left");
  $(".gShortForm input[type=submit]").addClass("ui-state-default ui-corner-right");

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]:not(.gShortForm input)").addClass("ui-state-default ui-corner-all");

  // Apply styles and icon classes to gContextMenu
  if ($(".gContextMenu").length) {
    $(".gContextMenu li").addClass("ui-state-default");
    $(".gContextMenu a").addClass("gButtonLink ui-icon-left");
    $(".gContextMenu a").prepend("<span class=\"ui-icon\"></span>");
    $(".gContextMenu a span").each(function() {
      var iconClass = $(this).parent().attr("class").match(/ui-icon-.[^\s]+/).toString();
      $(this).addClass(iconClass);
    });
  }

  // Album view only
  if ($("#gAlbumGrid").length) {
    // Vertical align thumbnails/metadata in album grid
    $(".gItem").gallery_valign();

    // Initialize context menus
    $(".gItem").hover(
      function(){
        var position = $(this).position();
        var item_classes = $(this).attr("class");
        var bg_color = $(this).css("background-color");
        var container = $(this).parent();
        $("#gHoverItem").remove();
        container.append("<div id=\"gHoverItem\"><div class=\"" + item_classes + "\">"
            + $(this).html() + "</div></div>");
        $("#gHoverItem").css("top", position.top);
        $("#gHoverItem").css("left", position.left);
        $("#gHoverItem").css("background-color", bg_color);
        $.fn.gallery_hover_init();
        var v_align = $(this).find(".gValign");
        var title = $(this).find("h2");
        var meta = $(this).find(".gMetadata");
        var context = $(this).find(".gContextMenu");
        var context_label = $(this).find(".gContextMenu li:first");
        $("#gHoverItem .gItem").height(
            $(v_align).gallery_height()
            + $(title).gallery_height()
            + $(meta).gallery_height()
            + parseInt($(context).css("margin-top").replace("px",""))
            + $(context_label).gallery_height()
          );

        $("#gHoverItem").fadeIn("fast");
        $("#gHoverItem").hover(
          function(){
            $(this).gallery_context_menu();
          },
          function() {
            $(this).hide();
          }
        );
      }
    );
  }

  // Photo/Item item view
  if ($("#gItem").length) {
    // Ensure the resized image fits within its container
    $("#gItem").gallery_fit_photo();

    // Initialize context menus
    var resize = $("#gItem").gallery_get_photo();
    $(resize).hover(function(){
      $(this).gallery_context_menu();
    });

    // Collapse comments form, insert button to expand
    if ($("#gAddCommentForm").length) {
      var showCommentForm = '<a href="#add_comment_form"'
        + ' class="showCommentForm gButtonLink ui-corner-all ui-icon-left ui-state-default right">'
        + '<span class="ui-icon ui-icon-comment"></span>' + ADD_A_COMMENT + '</a>';
      $("#gAddCommentForm").hide();
      $("#gComments").prepend(showCommentForm);
      $(".showCommentForm").click(function(){
        $("#gAddCommentForm").show(1000);
      });
    }

    // Add scroll effect for links to named anchors
    $.localScroll({
      queue: true,
      duration: 1000,
      hash: true
    });
  }

  // Initialize button hover effect
  $.fn.gallery_hover_init();

});
