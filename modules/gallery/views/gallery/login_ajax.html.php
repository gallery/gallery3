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

          // See comment about IE7 below
          setTimeout('$("#g-name").focus()', 100);
        }
      });
    });

    // Setting the focus here doesn't work on IE7, perhaps because the field is
    // not ready yet?  So set a timeout and do it the next time we're idle
    setTimeout('$("#g-username").focus()', 100);
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
    <? if (identity::is_writable() && !module::get_var("gallery", "maintenance_mode")): ?>
    <li>
      <a href="#" id="g-password-reset" class="g-right g-text-small"><?= t("Forgot your password?") ?></a>
    </li>
    <? endif ?>
  </ul>
</div>
