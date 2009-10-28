<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#g-login-form").ready(function() {
    $("#g-password-reset").click(function() {
      $.ajax({
        url: "<?= url::site("password/reset") ?>",
        success: function(data) {
          $("#g-login").html(data);
          $("#ui-dialog-title-g-dialog").html(<?= t("Reset password")->for_js() ?>);
          $(".submit").addClass("g-button ui-state-default ui-corner-all");
          $(".submit").gallery_hover_init();
          ajaxify_login_reset_form();
        }
      });
    });
  });

  function ajaxify_login_reset_form() {
    $("#g-login form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.form) {
          $("#g-login form").replaceWith(data.form);
          ajaxify_login_reset_form();
        }
        if (data.result == "success") {
          $("#g-dialog").dialog("close");
          window.location.reload();
        }
      }
    });
  };
</script>
<div id="g-login">
  <ul>
    <li id="g-login-form">
      <?= $form ?>
    </li>
    <? if (identity::is_writable()): ?>
    <li>
      <a href="#" id="g-password-reset" class="g-right g-txt-small"><?= t("Forgot your password?") ?></a>
    </li>
    <? endif ?>
  </ul>
</div>
