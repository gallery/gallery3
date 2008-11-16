$("document").ready(function() {
  ajaxify_comment_form();
});

function ajaxify_comment_form() {
  $("#gCommentForm").ajaxForm({
    target: "#gCommentForm",
    complete: function(xhr, statusText) {
      ajaxify_comment_form();
      if (xhr.status == 201) {
        $.get(xhr.getResponseHeader("Location"), function(data, textStatus) {
		$("#gComment div.gBlockContent ul:first").append(data);
		$("#gComment div.gBlockContent ul:first li:last").hide().slideDown();
	      }
	);
	$("#gCommentForm").clearForm();
      }
    }
  });
}
