/**
 * Initialize jQuery UI and Gallery Plugin elements
 */

$(document).ready(function() {

  // Initialize Superfish menus
  $("ul.g-menu").addClass("sf-menu");
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    speed: 'fast'
  });
  $("#g-site-menu").css("display", "block");

  // Initialize status message effects
  $("#g-action-status li").gallery_show_message();

  // Initialize dialogs
  $("#g-login-link").addClass("g-dialog-link");
  $(".g-dialog-link").gallery_dialog();

  // Initialize view menu
  if ($("#g-view-menu").length) {
    $("#g-view-menu ul").removeClass("g-menu").removeClass("sf-menu");
    $("#g-view-menu a").addClass("ui-icon");
  }

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]:not(.g-short-form input)").addClass("ui-state-default ui-corner-all");

  // Apply styles and icon classes to g-context-menu
  if ($(".g-context-menu").length) {
    $(".g-context-menu li").addClass("ui-state-default");
    $(".g-context-menu a").addClass("g-button ui-icon-left");
    $(".g-context-menu a").prepend("<span class=\"ui-icon\"></span>");
    $(".g-context-menu a span").each(function() {
      var iconClass = $(this).parent().attr("class").match(/ui-icon-.[^\s]+/).toString();
      $(this).addClass(iconClass);
    });
  }

  // Album view only
  if ($("#g-album-grid").length) {
    // Set equal height for album items and vertically align thumbnails/metadata
    $('.g-item').equal_heights().gallery_valign();

    // Initialize thumbnail hover effect
    $(".g-item").hover(
      function() {
        // Insert a placeholder to hold the item's position in the grid
        var placeHolder = $(this).clone().attr("id", "g-place-holder");
        $(this).after($(placeHolder));
        // Style and position the hover item
        var position = $(this).position();
        $(this).css("top", position.top).css("left", position.left);
        $(this).addClass("g-hover-item");
        // Initialize the contextual menu
        $(this).gallery_context_menu();
        // Set the hover item's height
        $(this).height("auto");
        var context_menu = $(this).find(".g-context-menu");
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
        $(this).removeClass("g-hover-item");
        $("#g-place-holder").remove();
      }
    );
  }

  // Photo/Item item view
  if ($("#g-item").length) {
    // Ensure the resized image fits within its container
    $("#g-item").gallery_fit_photo();

    // Initialize context menus
    var resize = $("#g-item").gallery_get_photo();
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
