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

  // Album view only
  if ($("#gAlbumGrid").length) {
    // Vertical align thumbnails/metadata in album grid
    $(".gItem").gallery_valign();
    // Apply styles to gContextMenu
    $(".gContextMenu li").addClass("ui-state-default");
    $(".gContextMenu a").addClass("gButtonLink ui-icon-left");
    $(".gContextMenu a").prepend("<span class=\"ui-icon\"></span>");
    $(".gContextMenu a span").each(function() {
      var iconClass = $(this).parent().attr("class").match(/ui-icon-.[^\s]*/).toString();
      $(this).addClass(iconClass);
    });
  }

  // Photo/Item item view only
  if ($("#gItem").length) {
    // Ensure that sized image versions
    // fit inside their container
    sizedImage();

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

  // Add hover state for buttons
  $(".ui-state-default").hover(
    function(){
      $(this).addClass("ui-state-hover");
    },
    function(){
      $(this).removeClass("ui-state-hover");
    }
  );

  // Initialize context menus
  // @todo apply hover affect to links
  $(".gItem").hover(
    function(){
      var pos = $(this).position();
      var itemClasses = $(this).attr("class");
      var bgColor = $(this).css("background-color");
      var cont = $(this).parent();
      $("#gHoverItem").remove();
      cont.append("<div id=\"gHoverItem\"><div class=\"" + itemClasses + "\">" 
          + $(this).html() + "</div></div>");
      $("#gHoverItem").css("top", pos.top);
      $("#gHoverItem").css("left", pos.left);
      $("#gHoverItem").css("background-color", bgColor);
      $("#gHoverItem").fadeIn("fast");
      $("#gHoverItem").hover(
        function(){
          // Initialize context menus
          $(".gContextMenu ul").hide();
          $(".gContextMenu").hover(
            function() {
              $(this).find("ul").slideDown("fast");
              var dialogLinks = $(this).find(".gDialogLink");
              $(dialgoLinks).gallery_dialog();
            },
            function() {
              $(this).find("ul").slideUp("slow");
            }
          );
        },
        function() {
          $(this).hide();
        }
      );
    },
    function(){
    }
  );

});

/**
 * Reduce width of sized photo if it's wider than its parent container
 */
function sizedImage() {
  var containerWidth = $("#gItem").width();
  var oPhoto = $("#gItem img").filter(function() {
    return this.id.match(/gPhotoId-/);
  });
  if (containerWidth < oPhoto.width()) {
    var proportion = containerWidth / oPhoto.width();
    oPhoto.width(containerWidth);
    oPhoto.height(proportion * oPhoto.height());
  }
}
