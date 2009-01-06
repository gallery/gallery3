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
 * @todo Set ui-dialog-buttonpane button values equal to the original form button value
 * @todo Display loading animation on form submit
 */
function openDialog(element, on_success) {
  var sHref = $(element).attr("href");
  var sTitle = $(element).attr("title");
  var eDialog = '<div id="gDialog"></div>';

  $("body").append(eDialog);
  $("#gDialog").dialog({
    autoOpen: false,
    autoResize: false,
    draggable: true,
    height: "auto",
    width: "auto",
    modal: true,
    overlay: {
      opacity: 0.7,
      background: "black"
    },
    resizable: true,
    close: function (event, ui) {
      $("#gDialog").dialog("destroy").remove();
    }
  });
  loading("#gDialog");
  $("#gDialog").html(sHref);
  $.get(sHref, function(data) {
    loading("#gDialog");
    $("#gDialog").html(data);
    var parent = $("#gDialog").parent().parent();
    parent.css("opacity", "0.0");
    $("#gDialog").dialog("open");
    $("#ui-dialog-title-gDialog").html($("#gDialog fieldset legend:eq(0)").html());
    if (parent.width() < 400) {
      parent.css("width", 400);
    }
    parent.css({"position": "fixed",
		"top": $(window).height() / 2 - parent.height() / 2,
		"left": $(window).width() / 2 - parent.width() / 2,
		"opacity": "1.0"
	       });

    ajaxify_dialog = function() {
      $("#gDialog form").ajaxForm({
	dataType: "json",
	success: function(data) {
	  if (data.form) {
	    $("#gDialog form").replaceWith(data.form);
	    ajaxify_dialog();
	  }
	  if (data.result == "success") {
	    $("#gDialog").dialog("close");
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
    };
    ajaxify_dialog();
  });
  return false;
}

/**
 * Toggle the processing indicator, both large and small
 *
 * @param element ID to which to apply the loading class, including #
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
