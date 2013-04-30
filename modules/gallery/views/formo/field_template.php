<? // Open the <li> and add the label (if not hidden) ?>
<? if (!$hidden = $field->is_hidden()): ?>
  <? if ($error = $field->error()): ?>
    <li class="g-error">
  <? else: ?>
    <li>
  <? endif; ?>
  <? if ($label): ?>
    <label for="<?= $field->alias() ?>"><?= $label ?></label>
  <? endif; ?>
<? endif; ?>
<? // Render the input element ?>
<? if ($field->get("editable")): ?>
  <?= $field->open() . $field->render_opts() . $field->close() ?>
<? else: ?>
  <?= $field->val() ?>
<? endif; ?>
<? // Add errors and close the <li> (if not hidden) ?>
<? if (!$hidden): ?>
  <? if ($error): ?>
    <p class="g-message g-error">
      <?= $error ?>
    </p>
  <? endif; ?>
  </li>
<? endif; ?>
