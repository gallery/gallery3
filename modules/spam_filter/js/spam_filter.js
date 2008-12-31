$("document").ready(function() {
  ajaxify_spam_filter_form();
});

function ajaxify_spam_filter_form() {
  $("#gContent form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.form) {
        $("#gContent form").replaceWith(data.form);
        ajaxify_spam_filter_form();
      }
      if (data.result == "success") {
        window.location.reload();
      }
    }
  });
};
