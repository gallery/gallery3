function show_login() {
  $("#gLoginLink").css({display: "none"});
  $("#gLoginForm").css({display: "block"});  
  $("#gLoginClose").css({display: "inline"});
}

function close_login() {
  $("#gLoginLink").css({display: "inline"});  
  $("#gLoginForm").css({display: "none"});  
  $("#gLoginClose").css({display: "none"});
}