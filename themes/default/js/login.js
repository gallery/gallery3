$(document).ready(function() {
  $("#gLoginForm").submit(function() {
    process_login();
    return false;
  });
});

function show_login() {
  $("#gLoginLink").css({display: "none"});
  $("#gLoginClose").css({display: "inline"});
  var url = $("#gLoginForm").attr("formSrc");
  $.get(url, null, function(data, textStatus) {
    $("#gLoginForm").html(data);
    $("#gLoginForm").css({display: "block"});  
  });
}

function close_login() {
  $("#gLoginLink").css({display: "inline"});  
  $("#gLoginForm").css({display: "none"});  
  $("#gLoginForm").html("");  
  $("#gLoginClose").css({display: "none"});
  $("input#gUsername").val("");
  $("input#gPassword").val("");
}

function process_login() {
  var username = $("input#gUsername").val();
  var password = $("input#gPassword").val();
  var data = 'username=' + username + '&password=' +  password;
  $.ajax({
    url: $("#gLogin").attr("action"),
    type: "POST",
    data: data,
    dataType: "json",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert("textStatus: " + textStatus + "\nerrorThrown: " + errorThrown);
    },
    success: function(data, textStatus) {
      if (data.error_message != "") {
        $("#gLoginMessage").html(data.error_message);
        $("#gLoginMessage").css({display: "block"});
        $("#gLogin").addClass("gError");
      }
    }
  });
}