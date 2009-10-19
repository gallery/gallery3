<?php defined("SYSPATH") or die("No direct script access.") ?>
<script language="text/javascript">
  $("#g-add-tag-form").ready(function() {
    var url = $("#g-tag-cloud").attr("title") + "/autocomplete";
    $("#g-add-tag-form input:text").autocomplete(
      url, {
        max: 30,
        multiple: true,
        multipleSeparator: ',',
        cacheLength: 1
      }
    );
  });
</script>
<div id="g-tag-cloud" title="<?= url::site("tags") ?>">
  <?= $cloud ?>
</div>
<?= $form ?>