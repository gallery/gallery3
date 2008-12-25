$("document").ready(function() {
  ajaxify_comment_form();
});

function ajaxify_comment_form() {
  $("#gComments form").ajaxForm({
    dataType: "json",
    success: function(data) {
      $("#gComments form").replaceWith(data.form);
      ajaxify_comment_form();
      if (data.result == "success") {
        $.get(data.resource, function(data, textStatus) {
          $("#gComments .gBlockContent ul:first").append("<li>"+data+"</li>");
          $("#gComments .gBlockContent ul:first li:last").hide().slideDown();
        });
        $("#gComments form").clearForm();
      }
    }
  });
};
