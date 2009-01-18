<?php defined("SYSPATH") or die("No direct script access.") ?>
<script>
$("document").ready(function() {
  ajaxify_package_form();
});

function ajaxify_package_form() {
  $("#gPackageSQL").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $("#gSuccessMsg").html(data.message);
        $("#gSuccessMsg").removeClass("gHide");
        $("#gFailMsg").addClass("gHide");
      } else {
        $("#gFailMsg").html(data.message);
        $("#gFailMsg").removeClass("gHide");
        $("#gSuccessMsg").addClass("gHide");
      }
    }
  });
};

</script>
<fieldset>
  <legend>Create install.sql</legend>
  <p>Press the button to extract the initial database configuration.</p>
  <form id="gPackageSQL" action="<?= url::site("welcome/package") ?>" method="POST">
    <input type="Submit" value="Package" />
    <div id="gSuccessMsg" class="success gHide"></div>
    <div id="gFailMsg" class="error gHide"></div>
  </form>
</fieldset>
