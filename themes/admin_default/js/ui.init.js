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
  $(".gAvailable").addClass("ui-corner-all");
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
  var ePanel = '<div id="gPanel"></div>';

  if ($("#gPanel").length) {
    $("#gPanel").slideUp("slow");
    $("#gPanel *").remove();
    $("#gPanel").remove();
  }

  $(parent).append(ePanel);

  var panel = $(parent).children("#gPanel");

  showLoading("#gPanel");

  $(panel).html(sHref);
  panel.show().slideDown("slow");
  
  $.get(sHref, function(data) {
    $(panel).html(data);
    ajaxify_panel = function() {
      $("#gPanel form").ajaxForm({
        dataType: "json",
        success: function(data) {
          if (data.form) {
            $("#gPanel form").replaceWith(data.form);
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
      showLoading("#gPanel");
    };
    ajaxify_panel();
  });
  return false;
}
