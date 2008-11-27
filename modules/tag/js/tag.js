$("document").ready(function() {
  ajaxify_tag_form();
});

function ajaxify_tag_form() {
  $("form#gAddTag").ajaxForm({
    complete: function(xhr, statusText) {
      $("form#gAddTag").replaceWith(xhr.responseText);
      if (xhr.status == 201) {
        $.get($("#gTagCloud").attr("src"), function(data, textStatus) {
	  $("#gTagCloud").html(data);
	});
	$("form#gAddTag").clearForm();
      }
      ajaxify_tag_form();
    }
  });
}
