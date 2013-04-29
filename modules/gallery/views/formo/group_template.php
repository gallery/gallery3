<?= $field->open() ?>
  <fieldset>
    <? if ($label): ?>
    <legend><?= $label ?></legend>
    <? endif; ?>
    <? if ($field->html()): ?>
    <p>
      <?= $field->html() ?>
    </p>
    <? endif; ?>
    <ul>
      <? foreach ($field->as_array() as $child): ?>
      <?= $child->render() ?>
      <? endforeach; ?>
    </ul>
  </fieldset>
<?= $field->close() ?>
