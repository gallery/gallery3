$(document).ready(function(){

  // Add Superfish menu class
  $("#gSiteAdminMenu ul.gMenu").addClass("sf-menu sf-navbar");

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
 * @todo Set ui-dialog-buttonpane button values equal to the original form button value
 * @todo Display loading animation on form submit
 */
function openDialog(element) {
  var sHref = $(element).attr("href");
  var sTitle = $(element).attr("title");
  var eDialog = '<div id="gDialog"></div>';

  $("body").append(eDialog);
  var buttons = {};
  buttons["Submit"] = function() {
    $("#gDialog form").ajaxForm({
      dataType: "json",
      success: function(data) {
	if (data.form) {
          $("#gDialog form").replaceWith(data.form);
	}
	if (data.result == "success") {
          $("#gDialog").dialog("close");
	  if (data.location) {
	    window.location = data.location;
	  } else {
	    window.location.reload();
	  }
	}
      }
    }).submit();
  };
  buttons["Reset"] = function() {
    $("#gDialog form").reset();
  };

  $("#gDialog").dialog({
    autoResize: false,
    draggable: true,
    height: $(window).height() - 40,
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
  loading("#gDialog");
  $(".ui-dialog-content").height(400);
  $("#gDialog").html(sHref);
  $.get(sHref, function(data) {
    loading("#gDialog");
    $("#gDialog").html(data).hide().fadeIn();
    // Get dialog and it's contents' height
    var contentHt =  $(".ui-dialog-titlebar").height()
        + $(".ui-dialog-content form").height()
        + $(".ui-dialog-buttonpane").height()
        + 60;
    // Resize height if content's shorter than dialog
    if (contentHt < $("#gDialog").data("height.dialog")) {
      $(".ui-dialog").animate({height: contentHt});
    };
  });
  return false;
}


/**
 * Toggle the processing indicator, both large and small
 *
 * @param element ID to which to apply the loading class, including #
 * @param size Either Large or Small
 */
function loading(element) {
  var size;
  switch (element) {
  	case "#gDialog":
  		size = "Large";
  		break;
  	default:
		size = "Small";
		break;
  }
  $(element).toggleClass("gLoading" + size);
}
