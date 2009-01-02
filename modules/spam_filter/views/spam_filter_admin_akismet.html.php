<?php defined("SYSPATH") or die("No direct script access.");?>
<li <? if (!empty($errors["api_key"])): ?> class="gError" <? endif ?>>
  <label for="api_key"><?= _("Api Key")?></label>
  <input name="api_key" id="gApiKey" class="textbox" type="text" value="<?= $api_key ?>" />
  <? if (!empty($errors["api_key"]) && $errors["api_key"] == "required"): ?>
  <p class="gError"><?= _("Api Key is required.") ?>
  <? endif ?>
  <? if (!empty($errors["api_key"]) && $errors["api_key"] == "invalid"): ?>
  <p class="gError"><?= _("Api Key is invalid.") ?>
  <? endif ?>
</li>

