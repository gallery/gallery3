$(document).ready(function(){
  // Initialize Superfish menus
  $("#gSiteAdminMenu ul.gMenu").addClass("sf-menu");
  $("ul.gMenu").addClass("sf-menu");
  $("ul.sf-menu").superfish({
    delay: 500,
    animation: {
      opacity: "show",
      height: "show"
    },
    pathClass: "current",
    speed: "fast"
  });
  $("#gSiteAdminMenu").css("display", "block");

  // Initialize status message effects
  $("#gMessage li").gallery_show_message();

  // Initialize modal dialogs
  $(".gDialogLink").gallery_dialog();

  // Initialize ajax links
  $(".gDialogLink").gallery_ajax();

  // Initialize panels
  $(".gPanelLink").gallery_panel();

  if ($("#gPhotoStream").length) {
    // Vertically align thumbs in photostream
    $(".gItem").gallery_valign();
  }

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]:not(.gShortForm input)").addClass("ui-state-default ui-corner-all");

  // Round view menu buttons
  if ($("#gAdminCommentsMenu").length) {
    $("#gAdminCommentsMenu ul").removeClass("gMenu").removeClass("sf-menu");
    $("#gAdminCommentsMenu").addClass("gButtonSet");
    $("#gAdminCommentsMenu a").addClass("gButtonLink ui-state-default");
    $("#gAdminCommentsMenu ul li:first a").addClass("ui-corner-left");
    $("#gAdminCommentsMenu ul li:last a").addClass("ui-corner-right");
  }

  // Round corners
  $(".gSelected").addClass("ui-corner-all");
  $(".gAvailable .gBlock").addClass("ui-corner-all");
  $(".gUnavailable").addClass("ui-corner-all");

  // Add hover state for buttons
  $(".ui-state-default").hover(
    function() {
      $(this).addClass("ui-state-hover");
    },
    function() {
      $(this).removeClass("ui-state-hover");
    }
  );
});
