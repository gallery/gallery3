$("document").ready(function() {
  ajaxify_tag_form();
});

function ajaxify_tag_form() {
  $("#gTag form").ajaxForm({
    dataType: "json",
    success: function(data) {
      $("#gTag form").replaceWith(data.form);
      ajaxify_tag_form();
      if (data.result == "success") {
        $.get($("#gTagCloud").attr("src"), function(data, textStatus) {
	  $("#gTagCloud").html(data);
	});
      }
      $("#gTag form").clearForm();
    }
  });
}
