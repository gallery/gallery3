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

function show_form(formName) {
  $(formName + "Link").css({display: "none"});
  $(formName + "Text").css({display: "inline"});
  $(formName + "Close").css({display: "inline"});
  var url = $(formName + "Form").attr("formSrc");
  $.get(url, null, function(data, textStatus) {
    $(formName + "Form").html(data);
    $(formName + "Form").css({display: "block"});  
  });
}

function hide_form(formName) {
  $(formName + "Link").css({display: "inline"});  
  $(formName + "Form").css({display: "none"});  
  $(formName + "Form").html("");  
  $(formName + "Text").css({display: "none"});
  $(formName + "Close").css({display: "none"});
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