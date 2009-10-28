<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-akismet" class="g-block">
  <h1> <?= t("Akismet spam filtering") ?> </h1>
  <p>
  <?= t("Akismet is a free, automated spam filtering service.  In order to use it, you need to sign up for a <a href=\"%api_key_url\">Wordpress.com API Key</a>, which is also free.  Your comments will be automatically relayed to <a href=\"%akismet_url\">Akismet.com</a> where they'll be scanned for spam.  Spam messages will be flagged accordingly and hidden from your vistors until you approve or delete them.",
        array("api_key_url" => "http://wordpress.com/api-keys",
              "akismet_url" => "http://akismet.com")) ?>
  </p>
  <div class="g-block-content">
    <? if ($valid_key): ?>
    <div class="g-module-status g-success">
      <?= t("Your API key is valid.  Your comments will be filtered!") ?>
    </div>
    <? endif ?>

    <?= $form ?>
  </div>
</div>
