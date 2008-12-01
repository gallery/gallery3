$("document").ready(function() {
  ajaxify_tag_form();
});

function ajaxify_tag_form() {
  $("#gAddTagForm").ajaxForm({
    complete: function(xhr, statusText) {
      $("#gAddTagForm").replaceWith(xhr.responseText);
      if (xhr.status == 201) {
        $.get($("#gTagCloud").attr("src"), function(data, textStatus) {
	  $("#gTagCloud").html(data);
	});
	$("#gAddTagForm").clearForm();
      }
      ajaxify_tag_form();
    }
  });
}
