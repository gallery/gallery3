$("document").ready(function() {
  $("#g-admin-comment-button").click(function(event) {
    event.preventDefault();
    if (!$("#g-comment-form").length) {
      $.get($(this).attr("href"),
	    {},
	    function(data) {
	      $("#g-comment-detail").append(data);
	      ajaxify_comment_form();
	    });
    }
  });
  $("#g-no-comments").click(function(event) {
    event.preventDefault();
    if (!$("#g-comment-form").length) {
      $.get($(this).attr("href"),
	    {},
	    function(data) {
	      $("#g-comment-detail").append(data);
	      ajaxify_comment_form();
	    });
      $("#g-no-comments-yet").remove();
    }
  });
});

function ajaxify_comment_form() {
  $("#g-comments form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.form) {
        $("#g-comments form").replaceWith(data.form);
        ajaxify_comment_form();
      }
      if (data.result == "success" && data.resource) {
        $.get(data.resource, function(data, textStatus) {
          $("#g-comments .g-block-content ul:first").append("<li>"+data+"</li>");
          $("#g-comments .g-block-content ul:first li:last").effect("highlight", {color: "#cfc"}, 8000);
          $("#g-comment-form").hide(2000).remove();
	  $("#g-no-comments-yet").hide(2000);
        });
      }
    }
  });
}
