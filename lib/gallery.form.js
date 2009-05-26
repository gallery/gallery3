/**
 * Handle initialization of all short forms
 *
 * @param shortForms array Array of short form IDs
 */
function handleShortFormEvent(shortForms) {
  for (var i in shortForms) {
    shortFormInit(shortForms[i]);
  }
}

/**
 * Initialize a short form. Short forms may contain only one text input.
 *
 * @param formID string The form's ID, including #
 */
function shortFormInit(formID) {
  $(formID).addClass("gShortForm");

  // Get the input ID and it's label text
  var labelValue = $(formID + " label:first").html();
  var inputID = "#" + $(formID + " input[type=text]:first").attr("id");

  // Set the input value equal to label text
  if ($(inputID).val() == "") {
    $(inputID).val(labelValue);
  }

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
