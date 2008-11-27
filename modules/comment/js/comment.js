$("document").ready(function() {
  ajaxify_comment_form();
});

function ajaxify_comment_form() {
  $("#gComments form").ajaxForm({
    complete: function(xhr, statusText) {
      $("#gComments form").replaceWith(xhr.responseText);
      if (xhr.status == 201) {
        $.get(xhr.getResponseHeader("Location"), function(data, textStatus) {
          $("#gComments .gBlockContent ul:first").append("<li>"+data+"</li>");
          $("#gComments .gBlockContent ul:first li:last").hide().slideDown();
        });
        $("#gComments form").clearForm();
      }
      ajaxify_comment_form();
    }
  });
}
