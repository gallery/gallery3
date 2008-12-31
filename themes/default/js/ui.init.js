/**
 * Initialize jQuery UI and Plugin elements
 * 
 * @todo Standardize how elements requiring listeners are identified (class or id)
 */

var shortForms = new Array(
  "#gSearchForm",
  "#gAddTagForm"
);

$("document").ready(function() {

  // Album view only
  if ($(".gItem").length) {
    // Vertical align thumbnails/metadata in album grid
    $(".gItem").wrapInner("<div></div>")
    $('.gItem div').vAlign();
  }
  
  // Photo/Item item view only
  if ($("#gItem").length) {
    sizedImage();
  }

  // Apply Superfish menus
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

  // Apply modal dialogs
  $(".gMenuLink").addClass("gDialogLink");
  $("#gLoginLink").addClass("gDialogLink");
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", {element: dialogLinks[i]}, handleDialogEvent);
  };

  // Short forms
  handleShortFormEvent(shortForms);
  
});

// Vertically align a block element's content
$.fn.vAlign = function() {
  return this.each(function(i){
    var ah = $(this).height();
    var ph = $(this).parent().height();
    var mh = (ph - ah) / 2;
    $(this).css('margin-top', mh);
  });
};

/**
 * Reduce width of sized photo if it's wider than its parent container
 */
function sizedImage() {
  var containerWidth = $("#gItem").width();
  var oPhoto = $("#gItem img").filter(function() {
    return this.id.match(/gPhotoID-/);
  });
  if (containerWidth < oPhoto.width()) {
    var proportion = containerWidth / oPhoto.width();
    oPhoto.width(containerWidth);
    oPhoto.height(proportion * oPhoto.height());
  }
}

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
    $("#gDialog form").resetForm();
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
 * Handle initialization of all short forms
 *
 * @param array shortForms Array of short form IDs
 */
function handleShortFormEvent(shortForms) {
  for (var i in shortForms) {      
    shortFormInit(shortForms[i]);
  }
}
 
/**
 * Initialize a short form. Short forms may contain only one text input.
 *
 * @param string formID The form's ID, including #
 */
function shortFormInit(formID) {
  // Get the input ID and it's label text
  var labelValue = $(formID + " label:first").html();
  var inputID = "#" + $(formID + " input[type='text']:first").attr("id");
 
  // Set the input value equal to label text
  $(inputID).val(labelValue);
  
  // Attach event listeners to the input
  $(inputID).bind("focus blur", function(e){
    var eLabelVal = $(this).siblings("label").html();
    var eInputVal = $(this).val();
 
    // Empty input value if it equals it's label
    if (eLabelVal == eInputVal) {
        $(this).val("");
    // Reset the input value if it's empty
    } else if ($(this).val() == "") {
      $(this).val(eLabelVal);
    }
  });
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
