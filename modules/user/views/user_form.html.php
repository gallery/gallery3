<?php defined("SYSPATH") or die("No direct script access.") ?>
<script  type="text/javascript">
  $("form").ready(function(){
    $('input[name="password"]').user_password_strength();
  });
</script>
<?= $form ?>
