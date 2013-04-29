<?= $field->open() ?>
  <? if ($title): ?>
  <?= $title ?>
  <? endif; ?>
  <? if (!$field->has_group()): ?>
  <ul>
  <? endif; ?>
    <? foreach ($field->as_array() as $child): ?>
    <?= $child->render() ?>
    <? endforeach; ?>
  <? if (!$field->has_group()): ?>
  </ul>
  <? endif; ?>
<?= $field->close() ?>
