<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminAkismet">
  <h1> <?= t("Akismet Spam Filtering") ?> </h1>
  <p>
    <?= t("Akismet is a free, automated spam filtering service.  In order to use it, you need to sign up for a <a href=\"http://wordpress.com/api-keys\">Wordpress.com API Key</a>, which is also free.  Your comments will be automatically relayed to <a href=\"http://akismet.com\">Akismet.com</a> where they'll be scanned for spam.  Spam messages will be flagged accordingly and hidden from your vistors until you approve or delete them.") ?>
  </p>

  <? if ($valid_key): ?>
  <div class="gSuccess">
    <?= t("Your API Key is valid.  Your comments will be filtered!") ?>
  </div>
  <? endif ?>

  <?= $form ?>
</div>
