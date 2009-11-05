function ajaxify_tag_form() {
  $("#g-tag form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $.get($("#g-tag-cloud").attr("title"), function(data, textStatus) {
          $("#g-tag-cloud").html(data);
        });
      }
      $("#g-tag form").resetForm();
    }
  });
}
