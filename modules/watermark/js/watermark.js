$("document").ready(function() {
  ajaxify_form();
});

function ajaxify_form() {
  $("#gUploadWatermarkForm").ajaxForm({
    complete:function(xhr, statusText) {
      $("#gUploadWatermarkForm").replaceWith(xhr.responseText);
      if (xhr.status == 200) {
        $("#gUploadWatermarkForm").clearForm();
      }
      ajaxify_form();
    }
  });
}
