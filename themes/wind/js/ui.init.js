/**
 * Initialize jQuery UI and Gallery Plugin elements
 */

var short_forms = new Array(
  "#gQuickSearchForm",
  "#gAddTagForm",
  "#gSearchForm"
);

$(document).ready(function() {

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
  for (var i in short_forms) {
    short_form_init(short_forms[i]);
    $(short_forms[i]).addClass("gShortForm");
  }
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

    // Initialize thumbnail hover effect
    $(".gItem").hover(
      function() {
        // Insert a placeholder to hold the item's position in the grid
        var placeHolder = $(this).clone().attr("id", "gPlaceHolder");
        $(this).after($(placeHolder));
        // Style and position the hover item
        var position = $(this).position();
        $(this).css("top", position.top).css("left", position.left);
        $(this).addClass("gHoverItem");
        // Initialize the contextual menu
        $(this).gallery_context_menu();
        // Set the hover item's height
        $(this).height("auto");
        var context_menu = $(this).find(".gContextMenu");
        var adj_height = $(this).height() + context_menu.height(); 
        $(this).height(adj_height); 
      },
      function() {
        // Reset item height and position
        if ($(this).next().height()) {
          var sib_height = $(this).next().height();
        } else {
          var sib_height = $(this).prev().height();
        }
        if ($.browser.msie && $.browser.version >= 8) {
          sib_height = sib_height + 1;
        }
        $(this).css("height", sib_height);
        $(this).css("position", "relative");
        $(this).css("top", 0).css("left", 0);
        // Remove the placeholder and hover class from the item
        $(this).removeClass("gHoverItem");
        $("#gPlaceHolder").remove();
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
