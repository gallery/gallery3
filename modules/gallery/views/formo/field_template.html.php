<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // Open the <li>, then add the label (if applicable) ?>
<? if (!$hidden = $field->is_hidden()): ?>
  <? if ($error = $field->error()): ?>
    <li class="g-error">
  <? else: ?>
    <li>
  <? endif; ?>
  <? if ($label && $field->get("editable")): ?>
    <label for="<?= $field->attr("id") ?>"><?= $label ?></label>
  <? endif; ?>
<? endif; ?>
<? // Render the input element ?>
<? if ($field->get("editable")): ?>
  <?= $field->open() . $field->render_opts() . $field->close() ?>
<? else: ?>
  <?= $field->val() ?>
<? endif; ?>
<? // Add errors, then close <li> (if applicable) ?>
<? if (!$hidden): ?>
  <? if ($error): ?>
    <p class="g-message g-error">
      <?= $error ?>
    </p>
  <? endif; ?>
  </li>
<? endif; ?>
