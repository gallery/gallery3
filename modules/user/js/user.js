/**
 * @todo preventDefault() not working in IE 6 and 7
 * @todo Close link should be reusable
 */

$("document").ready(function() {
  $("#gLoginLink").click(function(event){
    var url = $("#gLoginLink a").attr("href");
    $.get(url, function(data) {
	  $('#gLoginLink').hide();
	  $("#gLoginMenu").append('<li><a href="#">X</a></li>');
	  $("#gLoginMenu li:last").addClass("gClose").show();
	  $("#gLoginMenu .gClose a").click(function(){
	    $("#gLoginForm").remove();
	    $("#gLoginMenu .gClose").remove();
	    $("#gLoginLink").show();
	    $("input#gUsername").val("");
	    $("input#gPassword").val("");
	  });
      $("#gLoginFormContainer").html(data).hide().fadeIn();
      ajaxify_login_form();
    });
    return false;
  });
});

function ajaxify_login_form() {
  $("form#gLoginForm").ajaxForm({
    target: "#gLoginFormContainer",
    success: function(responseText, statusText) {
      if (!responseText) {
        window.location.reload();
      } else {
        ajaxify_login_form();
      }
    }
  });
}
