<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-recaptcha"></div>
<script type="text/javascript" src="<?php echo  request::protocol() ?>://www.google.com/recaptcha/api/js/recaptcha_ajax.js">
</script>
<script type="text/javascript">
  setTimeout(function() {
    Recaptcha.create(
      "<?php echo  $public_key ?>",
      "g-recaptcha",
      {
        theme: "white",
        custom_translations : { instructions_visual : <?php echo  t("Type words to check:")->for_js() ?>},
        callback: Recaptcha.focus_response_field
      }
    );
  }, 500);
</script>

