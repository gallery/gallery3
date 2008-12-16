/**
 * Initialize jQuery UI and Plugin elements
 */
$("document").ready(function() {

  // Apply modal dialog class
  $(".gMenuLink").addClass("gDialogLink");
  $("#gLoginLink").addClass("gDialogLink");

  // Add Superfish menu class
  $("ul.gMenu").addClass("sf-menu");
  $("ul#gViewMenu").addClass("sf-menu");
  
  // Superfish menu options
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    speed: 'fast'
  });

  // Reduce width of sized photo if it is wider than its parent container
  if ($("#gItem").length) {
    var containerWidth = $("#gItem").width();
    var oPhoto = $("#gItem img").filter(function() {
      return this.id.match(/gPhotoID-/);
    })
    if (containerWidth < oPhoto.width()) {
      var proportion = containerWidth / oPhoto.width();
      oPhoto.width(containerWidth);
      oPhoto.height(proportion * oPhoto.height());
    }
  }

  /**
   * Attach event listeners to open modal dialogs
   */
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", {element: dialogLinks[i]}, handleDialogEvent);
  };
  
});

/**
 * Fire openDialog() and prevent links from opening
 * 
 * @see openDialog()
 */
function handleDialogEvent(event) {
  openDialog(event.data.element);
  event.preventDefault();
}

/**
 * Display modal dialogs, populate dialog with trigger link's title and href	
 * 
 * @requires ui.core
 * @requires ui.draggable
 * @requires ui.resizable
 * @requires ui.dialog
 * @see handleDialogEvent()
 * 
 * @todo Set dialog attributes dynamically (width, height, drag, resize)
 */
function openDialog(element) {
  var sHref = $(element).attr("href");
  var sTitle = $(element).attr("title");
  var eDialog = '<div id="gDialog"></div>';

  $("body").append(eDialog);
  var buttons = {};
  buttons["Submit"] = function() {
    var form = $("#gDialog").find("form");
    var options = 
    $(form).ajaxSubmit({
      success: function(data, textStatus) {
        if (data == "") {
          window.location.reload()
          $("#gDialog").dialog("close");
        }
        $("#gDialog").html(data);
      }
    });
  }
  buttons["Reset"] = function() {
    var form = $("#gDialog").find("form");
    form[0].reset();
  }

  $("#gDialog").dialog({
    autoResize: false,
    draggable: true,
    height: 500,
    modal: true,
    overlay: {
      opacity: 0.7,
      background: "black"
    },
    resizable: true,
    title: sTitle,
    width: 600,
    buttons: buttons,
    close: function (event, ui) {
      $("#gDialog").dialog('destroy').remove();
    }
  });
  $("#gDialog").html(sHref);
  $.get(sHref, function(data) {
    $("#gDialog").html(data);
  });
  return false;
}
