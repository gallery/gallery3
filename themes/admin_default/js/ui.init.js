$(document).ready(function(){

  // Add Superfish menu class
  $("#gSiteAdminMenu ul.gMenu").addClass("sf-menu");
  $("ul.gMenu").addClass("sf-menu");

  // Superfish menu options
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    pathClass:  'current',
    speed: 'fast'
  });

  $(".gBlock h2").addClass("gDraggable");

  // Apply modal dialogs
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", {element: dialogLinks[i]}, handleDialogEvent);
  };
  
  $("#gThemeAdmin :radio").click(function(event) {
      $("#gThemeDetails").load("themes/edit/" + event.target.value);
  });
  
  $("#gThemeTabs > ul").tabs({ select: updateThemeDetails });
});

function updateThemeDetails(evt, ui) {
  var themeName;
  if (ui.index == 0) {
    themeName = $("#gtRegular :checked").val();
  } else {
    themeName = $("#gtAdmin :checked").val();
  }
  $("#gThemeDetails").load("themes/edit/" + themeName);
}
