$("document").ready(function() {
  ajaxify_tag_form();
});

function ajaxify_tag_form() {
  $("#gTag form").ajaxForm({
    complete: function(xhr, statusText) {
      $("#gTag form").replaceWith(xhr.responseText);
      if (xhr.status == 201) {
        $.get($("#gTagCloud").attr("src"), function(data, textStatus) {
	  $("#gTagCloud").html(data);
	});
	$("#gTag form").clearForm();
      }
      ajaxify_tag_form();
    }
  });
}
