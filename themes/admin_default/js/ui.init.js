$(document).ready(function(){
  // Add Superfish menu class
  $("#gSiteAdminMenu ul.gMenu").addClass("sf-menu");
  $("ul.gMenu").addClass("sf-menu");

  // Superfish menu options
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity: 'show',
      height: 'show'
    },
    pathClass: 'current',
    speed: 'fast'
  });

  // Apply modal dialogs
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", {element: dialogLinks[i]}, handleDialogEvent);
  }

  $("#gThemeDetailsForm").ajaxForm( {
    dataType: "json",
    success: function(body, result, set) {
      if (body.result == "success") {
        $("#gMessage").append("<span class='gSuccess'>" + body.message + "</span>");
      } else {
        $("#gMessage").append("<span class='gError'>" + body.message + "</span>");
      }
  }});

  // Sortable dashboard blocks
  if ($(".ui-dialog-titlebar-close").length) {
    $(".gBlock *:first").addClass("gDraggable");
	  $("#gContent").sortable({
      connectWith: ["#gSidebar"],
      cursor: "move",
      handle: $("div:first"),
      opacity: 0.6,
      placeholder: "gDropTarget",
	    update: function() { console.log($(this).sortable("serialize")); }
      // @todo stop: function() { .ajax() }
    });
    $("#gSidebar").sortable({
      connectWith: ["#gContent"],
      containment: "document",
      cursor: "move",
      handle: $("div:first"),
      opacity: 0.6,
      placeholder: "gDropTarget",
      update: function() { console.log($(this).sortable("serialize")); }
      // @todo stop: function() { .ajax() }
    });
  }
});
