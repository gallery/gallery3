$("gUploadWatermarkForm").ready(function() {
  ajaxify_watermark_add_form();
});

function ajaxify_watermark_add_form() {
  $("#gUploadWatermarkForm").ajaxForm({
    complete:function(xhr, statusText) {
      $("#gUploadWatermarkForm").replaceWith(xhr.responseText);
      ajaxify_watermark_add_form();
    }
  });
}
