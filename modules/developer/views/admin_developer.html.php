<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= html::script("modules/developer/js/developer.js") ?>
<div id="gDeveloper">
  <h2>
    <?= t("Developer Tools") ?>
  </h2>
  <div id="gDeveloperTools">
    <ul>
      <li><a href="#create-module"><span><?= t("Create new module") ?></span></a></li>
    </ul>
    <div id="#create-module">
      <?= $module_create ?>
    </div>
  </div>
</div>
