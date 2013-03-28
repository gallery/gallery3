$("document").ready(function() {
  $("#g-add-comment").click(function(event) {
    event.preventDefault();
    if (!$("#g-comment-form").length) {
      $.get($(this).attr("href"),
	    {},
	    function(data) {
	      $("#g-comment-detail").append(data);
	      ajaxify_comment_form();
        $.scrollTo("#g-comment-form-anchor", 800);
	    });
    }
  });
  $(".g-no-comments a").click(function(event) {
    event.preventDefault();
    if (!$("#g-comment-form").length) {
      $.get($(this).attr("href"),
	    {},
	    function(data) {
	      $("#g-comment-detail").append(data);
	      ajaxify_comment_form();
	    });
      $(".g-no-comments").remove();
    }
  });
});

function ajaxify_comment_form() {
  $("#g-comments form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $("#g-comments #g-comment-detail ul").append(data.view);
        $("#g-comments #g-comment-detail ul li:last").effect("highlight", {color: "#cfc"}, 8000);
        $("#g-comment-form").hide(2000).remove();
        $("#g-no-comments").hide(2000);
      } else {
        if (data.form) {
          $("#g-comments form").replaceWith(data.form);
          ajaxify_comment_form();
        }
      }
    }
  });
}
