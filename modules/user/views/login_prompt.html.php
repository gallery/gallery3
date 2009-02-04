<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#gLoginForm").ready(function() {
    $("#gForgotPasswordLink").click(function() {
      $.ajax({
        url: "<?= url::site("password/reset") ?>",
        success: function(data) {
          $("div#gLoginView").html(data);
          $("#ui-dialog-title-gDialog").text("<?= t("Reset Password") ?>");
          ajaxify_login_reset_form();
        }
      });
    });
  });

  function ajaxify_login_reset_form() {
    $("#gLoginView form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.form) {
          $("#gLoginView form").replaceWith(data.form);
          ajaxify_login_reset_form();
        }
        if (data.result == "success") {
          $("#gDialog").dialog("close");
          window.location.reload();
        }

      }
    });
  };
</script>
<div id="gLoginView">
  <ul>
    <li>
      <div id="gLoginViewForm">
        <?= $form ?>
      </div>
    </li>
    <li>
  <a href="#" id="gForgotPasswordLink"><?= t("Forgot your Password?") ?></a>
    </li>
  </ul>
</div>
