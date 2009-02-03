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

  // Apply hide/show functionality on user admin view
  var panelLinks = $(".gPanelLink");
  for (i=0; i<panelLinks.length; i++) {
    $(panelLinks[i]).bind("click", {element: panelLinks[i]}, handlePanelEvent);
  }

  // Round corners
  $(".gSelected").addClass("ui-corner-all");
  $(".gAvailable .gBlock").addClass("ui-corner-all");
  $(".gUnavailable").addClass("ui-corner-all");

  // Add drop shadows
  $(".gSelected").dropShadow();

});

function handlePanelEvent(event) {
  togglePanel(event.data.element);
  event.preventDefault();
}

function togglePanel(element, on_success) {
  var parent = $(element).parent().parent();
  var sHref = $(element).attr("href");
  var parentClass = $(parent).attr("class");
  var ePanel = '<tr id="gPanel"><td colspan="6"></td></tr>';

  if ($("#gPanel").length) {
    $("#gPanel").slideUp("slow");
    $("#gPanel *").remove();
    $("#gPanel").remove();
    console.log("Removing existing #gPanel");
    //togglePanel(element, on_success);
  } else {
    console.log("Adding #gPanel");
    $(parent).after(ePanel);
    //showLoading("#here");
    $("#gPanel td").html(sHref);
    $("#gPanel").addClass(parentClass).show().slideDown("slow");
    $.get(sHref, function(data) {
      $("#gPanel td").html(data);
      ajaxify_panel = function() {
        $("#gPanel td form").ajaxForm({
          dataType: "json",
          success: function(data) {
            if (data.form) {
              $("#gPanel td form").replaceWith(data.form);
              ajaxify_panel();
            }
            if (data.result == "success") {
              if (on_success) {
                on_success();
              } else if (data.location) {
                window.location = data.location;
              } else {
                window.location.reload();
              }
            }
          }
        });
        if ($("#gPanel td").hasClass("gLoadingLarge")) {
          showLoading("#gPanel td");
        }
      };
      ajaxify_panel();
    });
  }
  return false;
}
