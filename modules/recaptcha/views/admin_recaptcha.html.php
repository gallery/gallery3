<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminRecaptcha">
  <h1> <?= t("ReCaptcha Challenge Filtering") ?> </h1>
  <p>
    <?= t("Recaptcha is a free CAPTCHA service that helps to digitize books, newspapers and old time radio shows. automated spam filtering service.  In order to use it, you need to sign up for a <a href=\"{$form->get_key_url}\">ReCaptcha Public/Private Key pair</a>, which is also free.  Once registered, the the challenge and response strings are evaluated at <a href=\"%url\">recaptcha.net</a> to determine if the form content has been entered by a bot.", array("url" => "http://recaptcha.net")) ?>
  </p>

     <?= $form ?>
</div>

<? if ($public_key && $private_key): ?>
<div id="gAdminRecaptchaTest" class="gBlock">
  <h2> <?= t("Recaptcha Test") ?> </h2>
  <p>
    <?= t("If you see a captcha form below, then Recaptcha is functioning properly.") ?>
  </p>

  <div id="gRecaptcha"/>
  <script type="text/javascript" src="http://api.recaptcha.net/js/recaptcha_ajax.js"></script>
  <script type="text/javascript">
    Recaptcha.create("<?= $public_key ?>", "gRecaptcha", {
      callback: Recaptcha.focus_response_field,
      lang: "en",
      theme: "white"
    });
  </script>
  </div>
</div>
<? endif ?>

