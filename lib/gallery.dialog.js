/**
 * Fire openDialog() and prevent links from opening
 * @see openDialog()
 */
function handleDialogEvent(event) {
  openDialog(event.currentTarget);
  event.preventDefault();
}

/**
 * Display modal dialogs, populate dialog with trigger link's href
 * @requires ui.core
 * @requires ui.draggable
 * @requires ui.resizable
 * @requires ui.dialog
 * @see handleDialogEvent()
 * @see showLoading()
 */
function openDialog(element, on_success) {
  var sHref = $(element).attr("href");
  var sTitle = $(element).attr("title");
  var eDialog = '<div id="gDialog"></div>';

  $("body").append(eDialog);
  $("#gDialog").dialog({
    autoOpen: false,
    autoResize: true,
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
  showLoading("#gDialog");
  $.get(sHref, function(data) {
    showLoading("#gDialog");
    $("#gDialog").html(data);
    var parent = $("#gDialog").parent().parent();
    $("#gDialog").dialog("open");
    if ($("#gDialog h1").length) {
      sTitle = $("#gDialog h1:eq(0)").html();
    } else if ($("#gDialog fieldset legend").length) {
      sTitle = $("#gDialog fieldset legend:eq(0)").html();
    }
    $("#ui-dialog-title-gDialog").html(sTitle);
    if (parent.width() < 400) {
      parent.css("width", 200);
    }
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
 * @param elementID Target ID, including #, to apply .gLoadingSize
 */
function showLoading(elementID) {
  var size;
  switch (elementID) {
    case "#gDialog":
    case "#gPanel":
      size = "Large";
      break;
    default:
      size = "Small";
      break;
  }
  $(elementID).toggleClass("gLoading" + size);
}
