<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1 style="display:none"><?= t("Confirm task removal") ?></h1>
<p class="g-warning"><?= t("Really remove the task: %name", array("name" => $name)) ?></p>
<?= $form ?>
