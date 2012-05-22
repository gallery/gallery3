<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#g-add-tag-form").ready(function() {
    var url = $("#g-tag-cloud-autocomplete-url").attr("href");
    $("#g-add-tag-form input:text").gallery_autocomplete(
      url, {
        max: 30,
        multiple: true,
        multipleSeparator: ',',
        cacheLength: 1,
        selectFirst: false
      }
    );
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
