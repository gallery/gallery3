<?php defined("SYSPATH") or die("No direct script access.") ?>
<li <? if (!empty($errors["public_key"])): ?> class="gError" <? endif ?>>
  <label for="public_key"><?= _("Public Key")?></label>
  <input name="public_key" id="gPublicKey" class="textbox" type="text" value="<?= $public_key ?>" size="72" />
  <? if (!empty($errors["public_key"]) && $errors["public_key"] == "required"): ?>
  <p class="gError"><?= _("Public Key is required.") ?>
  <? endif ?>
  <? if (!empty($errors["public_key"]) && $errors["public_key"] == "invalid"): ?>
  <p class="gError"><?= _("Private Key / Public Key combination is invalid.") ?>
  <? endif ?>
</li>
<li <? if (!empty($errors["private_key"])): ?> class="gError" <? endif ?>>
  <label for="private_key"><?= _("Private Key")?></label>
  <input name="private_key" id="gPrivateKey" class="textbox" type="text" value="<?= $private_key ?>" size="72" />
  <? if (!empty($errors["private_key"]) && $errors["private_key"] == "required"): ?>
  <p class="gError"><?= _("Private Key is required.") ?>
  <? endif ?>
</li>

