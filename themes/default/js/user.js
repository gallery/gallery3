function show_login(url) {
  $("#gLoginLink").hide();
  $("#gLoginClose").show();
  $.get(url, function(data) {
    $("#gLoginFormContainer").html(data);
    ajaxify_login_form();
  });
}

function ajaxify_login_form() {
  $("#gLoginMenu form ul").addClass("gInline");
  $("form#gLogin").ajaxForm({
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
  $("#gLogin").remove();
  $("#gLoginClose").hide();
  $("#gLoginLink").show();
  $("input#gUsername").val("");
  $("input#gPassword").val("");
}
