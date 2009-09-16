/**
 * Handle initialization of all short forms
 *
 * @param shortForms array Array of short form IDs
 */
function handle_short_form_event(short_forms) {
  for (var i in short_forms) {
    short_form_init(short_forms[i]);
  }
}

/**
 * Initialize a short form. Short forms may contain only one text input.
 *
 * @param form_id string The form's CSS id
 */
function short_form_init(form_id) {
  var form = $(form_id);
  form.addClass("gShortForm");

  var label = form.find("label:first");
  var input = form.find("input[type=text]:first");
  var button = form.find("input[type=submit]");

  // Set the input value equal to label text
  if (input.val() == "") {
    input.val(label.html());
    button.enable(false);
  }

  // Attach event listeners to the input
  input.bind("focus", function(e) {
    // Empty input value if it equals it's label
    if ($(this).val() == label.html()) {
      $(this).val("");
    }
    button.enable(true);
  });

  input.bind("blur", function(e){
    // Reset the input value if it's empty
    if ($(this).val() == "") {
      $(this).val(label.html());
      button.enable(false);
    }
  });
}
