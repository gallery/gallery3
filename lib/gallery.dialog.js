/**
 * Fire openDialog() and prevent links from opening
 * @see openDialog()
 */
function handleDialogEvent(event) {
  var target = event.currentTarget;
  if (!target) {
    target = event.srcElement;
  }
  openDialog(target);
  event.preventDefault();
}

function ajaxify_dialog(on_success) {
  $("#gDialog form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.form) {
        $("#gDialog form").replaceWith(data.form);
        ajaxify_dialog(on_success);
        on_form_loaded();
        if (typeof data.reset == 'function') {
          eval(data.reset + '()');
        }
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
  // @todo Fix i18n for Cancel link
  var dialogWidth;

  $("body").append(eDialog);

  $("#gDialog").dialog({
    autoOpen: false,
    autoResize: true,
    modal: true,
    resizable: false,
    close: closeDialog
  });

  showLoading("#gDialog");

  $.get(sHref, function(data) {
    showLoading("#gDialog");
    $("#gDialog").html(data);
    var dialogHeight = $("#gDialog").height();
    var cssWidth = new String($("#gDialog form").css("width"));
    var childWidth = cssWidth.replace(/[^0-9]/g,"");
    if ($("#gDialog iframe").length) {
      dialogWidth = $(window).width() - 100;
      // Set the iframe width and height
      $("#gDialog iframe").width("100%");
      $("#gDialog iframe").height($(window).height() - 100);
    } else if (childWidth == "" || childWidth > 300) {
      dialogWidth = 500;
    }
    $("#gDialog").dialog('option', 'width', dialogWidth);

    on_form_loaded();

    $("#gDialog").dialog("open");
    // Remove titlebar for progress dialogs or set title
    if ($("#gDialog #gProgress").length) {
      $(".ui-dialog-titlebar").remove();
    } else if ($("#gDialog h1").length) {
      $("#gDialog").dialog('option', 'title', $("#gDialog h1:eq(0)").html());
    } else if ($("#gDialog fieldset legend").length) {
      $("#gDialog").dialog('option', 'title', $("#gDialog fieldset legend:eq(0)").html());
    }

    ajaxify_dialog(on_success);
  });
  return false;
}

function on_form_loaded() {
  var eCancel = '<a href="javascript: closeDialog()" class="gCancel">Cancel</a>';
  if ($("#gDialog .submit").length) {
    $("#gDialog .submit").addClass("ui-state-default ui-corner-all");
    $("#gDialog .submit").parent().append(eCancel);
  }
  $("#gDialog").dialog("option", "form", $("#gDialog form"));
  $("#gDialog .ui-state-default").hover(
    function() {
      $(this).addClass("ui-state-hover");
    },
    function() {
      $(this).removeClass("ui-state-hover");
    }
  );
}

function closeDialog() {
  $("#gDialog").dialog("option", "form").trigger("form_closing");
  $("#gDialog").dialog("destroy").remove();
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
