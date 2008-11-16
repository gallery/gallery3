$("document").ready(function() {
  ajaxify_comment_form();
});

function ajaxify_comment_form() {
  $("#gCommentForm").ajaxForm({
    complete: function(xhr, statusText) {
      $("#gCommentForm").replaceWith(xhr.responseText);
      if (xhr.status == 201) {
        $.get(xhr.getResponseHeader("Location"), function(data, textStatus) {
		$("#gComment div.gBlockContent ul:first").append(data);
		$("#gComment div.gBlockContent ul:first li:last").hide().slideDown();
	      }
	);
	$("#gCommentForm").clearForm();
      }
      ajaxify_comment_form();
    }
  });
}
