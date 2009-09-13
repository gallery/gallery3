<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gRecaptcha"></div>
<script type="text/javascript" src="http://api.recaptcha.net/js/recaptcha_ajax.js"></script>
<script type="text/javascript">
  setTimeout(function() {
    Recaptcha.create(
      "<?= $public_key ?>",
      "gRecaptcha",
      {
        theme: "white",
        custom_translations : { instructions_visual : <?= t("Type words to check:")->for_js() ?>},
        callback: Recaptcha.focus_response_field
      }
    );
  }, 0);
</script>

