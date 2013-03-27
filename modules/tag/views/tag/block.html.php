<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#g-add-tag-form").ready(function() {
    var url = $("#g-tag-cloud-autocomplete-url").attr("href");
    function split(val) {
      return val.split(/,\s*/);
    }
    function extract_last(term) {
      return split(term).pop();
    }
    $("#g-add-tag-form input:text").gallery_autocomplete(url, {multiple: true});
    $("#g-add-tag-form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.result == "success") {
          $("#g-tag-cloud").html(data.cloud);
        }
        $("#g-add-tag-form").resetForm();
      }
    });
  });
</script>
<div id="g-tag-cloud">
   <a id="g-tag-cloud-autocomplete-url" style="display: none"
      href="<?= url::site("tags/autocomplete") ?>"></a>
  <?= $cloud ?>
</div>
<?= $form ?>
