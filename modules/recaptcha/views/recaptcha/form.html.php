<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-recaptcha"></div>
<script type="text/javascript" src="<?= request::protocol() ?>://www.google.com/recaptcha/api/js/recaptcha_ajax.js">
</script>
<script type="text/javascript">
  setTimeout(function() {
    Recaptcha.create(
      "<?= $public_key ?>",
      "g-recaptcha",
      {
        theme: "white",
        custom_translations : { instructions_visual : <?= t("Type words to check:")->for_js() ?>},
        callback: Recaptcha.focus_response_field
      }
    );
  }, 500);
</script>

