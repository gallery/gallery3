<?php defined("SYSPATH") or die("No direct script access.") ?>
<div>
  <p>
    <?php echo t("The administration session has expired, please re-authenticate to access the administration area.") ?>
  </p>
  <p>
    <?php echo t("You are currently logged in as %user_name.", array("user_name" => $user_name)) ?>
  </p>
  <?php echo $form ?>
  <script type="text/javascript">
  $("#g-reauthenticate-form").ready(function() {
    $("#g-password").focus();
  });
  </script>
</div>
