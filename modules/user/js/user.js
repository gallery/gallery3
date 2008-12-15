/**
 * Ajaxify user login form
 */
function ajaxify_login_form(event) {
  event.preventDefault();
  $("#gLoginForm").ajaxForm({
    target: "#gDialog",
    success: function(responseText, statusText) {
      if (!responseText) {
        window.location.reload();
      } else {
        ajaxify_login_form(event);
      }
    }
  });
  return false;
}

$("document").ready(function() {
    $.listen("submit", "#gLoginForm", function(event) {
      ajaxify_login_form(event);
    });
});
