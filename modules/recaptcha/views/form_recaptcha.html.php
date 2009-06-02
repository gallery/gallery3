<div id="gRecaptcha"></div>
<script type="text/javascript" src="http://api.recaptcha.net/js/recaptcha_ajax.js"></script>
<script type="text/javascript">
  setTimeout(function() {
    Recaptcha.create(
      "<?= $public_key ?>",
      "gRecaptcha",
      { theme: "white", callback: Recaptcha.focus_response_field });
  }, 0);
</script>

