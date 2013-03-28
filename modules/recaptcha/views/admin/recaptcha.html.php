<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block">
  <h1> <?= t("reCAPTCHA challenge filtering") ?> </h1>
  <p>
    <?= t("reCAPTCHA is a free CAPTCHA service that helps to digitize books, newspapers and old time radio shows.  In order to use it, you need to sign up for a <a href=\"%domain_url\">reCAPTCHA Public/Private Key pair</a>, which is also free.  Once registered, the challenge and response strings are evaluated at <a href=\"%recaptcha_url\">recaptcha.net</a> to determine if the form content has been entered by a bot.",
          array("domain_url" => $form->get_key_url,
                "recaptcha_url" => html::mark_clean("http://recaptcha.net"))) ?>
  </p>

  <div class="g-block-content">
    <?= $form ?>

    <? if ($public_key && $private_key): ?>
    <div id="g-admin-recaptcha-test">
      <h2> <?= t("reCAPTCHA test") ?> </h2>
      <p>
        <?= t("If you see a CAPTCHA form below, then reCAPTCHA is functioning properly.") ?>
      </p>

      <div id="g-recaptcha">
      <script type="text/javascript" src="http://api.recaptcha.net/js/recaptcha_ajax.js"></script>
      <script type="text/javascript">
        Recaptcha.create("<?= $public_key ?>", "g-recaptcha", {
          callback: Recaptcha.focus_response_field,
          lang: "en",
          custom_translations : { instructions_visual : <?= t("Type words to check:")->for_js() ?>},
          theme: "white"
        });
      </script>
      </div>
    </div>
    <? endif ?>

  </div>
</div>
