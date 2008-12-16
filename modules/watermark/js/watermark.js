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

function watermark_dialog_initialize() {
  var container = $("#gDialog").parent().parent();
  var container_height = $(container).attr("offsetHeight");
  var container_width = $(container).attr("offsetWidth");

  var new_height = $("#gDialog").attr("offsetHeight") +
    container.find("div.ui-dialog-titlebar").attr("offsetHeight") +
    container.find("div.ui-dialog-buttonpane").attr("offsetHeight");
  var height = Math.max(new_height, container_height);
  var width = Math.max($("#gDialog").attr("offsetWidth"), container_width);
  container.css("height", height + "px");
  container.css("width", width + "px");
  container.css("top", ((document.height - height) / 2) + "px");
  container.css("left", ((document.width - width) / 2) + "px");
}
