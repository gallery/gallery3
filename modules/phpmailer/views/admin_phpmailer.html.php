<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-php-mailer-admin">

<?= t('To use this module you will need to ensure composer is installed then run "composer install" from the gallery3 directory.') ?>

  <h2> <?= t("PHPMailer Settings") ?> </h2>
  <?= $phpmailer_form ?>
</div>
