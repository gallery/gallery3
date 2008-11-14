$(document).ready(function() {
  $("#gLoginForm").submit(function() {
    process_login();
    return false;
  });
  $("#gLogoutLink").click(function() {
    process_logout();
    return false;
  });
});

function show_login() {
  $("#gLoginLink").css({display: "none"});
  $("#gLoginText").css({display: "inline"});
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
  $("#gLoginText").css({display: "none"});
  $("#gLoginClose").css({display: "none"});
  $("input#gUsername").val("");
  $("input#gPassword").val("");
}

function process_login() {
  $.ajax({
    url: $("#gLogin").attr("action"),
    type: "POST",
    data: $("#gLogin").serialize(),
    dataType: "json",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert("textStatus: " + textStatus + "\nerrorThrown: " + errorThrown);
    },
    success: function(data, textStatus) {
      if (data.error_message != "") {
        $("#gLoginMessage").html(data.error_message);
        $("#gLoginMessage").css({display: "block"});
        $("#gLogin").addClass("gError");
      } else {
        window.location.reload();
      }
    }
  });
}

function process_logout() {
  $.ajax({
    url: $("#gLogoutLink").attr("href"),
    type: "GET",
    dataType: "json",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert("textStatus: " + textStatus + "\nerrorThrown: " + errorThrown);
    },
    success: function(data, textStatus) {
      if (data.logout) {
        window.location.reload();
      }
    }
  });
}