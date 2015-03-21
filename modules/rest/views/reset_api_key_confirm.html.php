<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-rest-reset-api-key" class="ui-helper-clearfix">
  <p>
    <?php echo  t("Do you really want to reset your REST API key?  Any clients that use this key will need to be updated with the new value.") ?>
  </p>
  <?php echo  $form ?>
</div>
