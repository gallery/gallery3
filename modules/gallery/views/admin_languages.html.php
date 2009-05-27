<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gLanguages">
  <h2> <?= t("Languages") ?> </h2>

  <?= $settings_form ?>

  <h2> <?= t("Download translations") ?> </h2>
  <a href="<?= url::site("admin/maintenance/start/core_task::update_l10n?csrf=$csrf") ?>"
     class="gDialogLink">
    <?= t("Get updates") ?>
  </a>

  <h2> <?= t("Your Own Translations") ?> </h2>
  <?= $share_translations_form ?>
</div>
