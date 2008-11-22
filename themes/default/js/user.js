function show_login(url) {
  $("#gLoginLink").hide();
  $(".gClose").show();
  $.get(url, function(data) {
    $("#gLoginFormContainer").html(data);
    ajaxify_login_form();
  });
}

function ajaxify_login_form() {
  $("form#gLoginForm").ajaxForm({
    target: "#gLoginFormContainer",
    success: function(responseText, statusText) {
      if (!responseText) {
        window.location.reload();
      } else {
        ajaxify_login_form();
      }
    },
  });
}

function close_login() {
  $("#gLoginForm").remove();
  $(".gClose").hide();
  $("#gLoginLink").show();
  $("input#gUsername").val("");
  $("input#gPassword").val("");
}
