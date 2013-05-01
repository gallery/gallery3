<? // Render the group.  The code here is very similar to that of form_template. ?>
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
    <? $ul_open = false; ?>
    <? foreach ($field->as_array() as $child): ?>
      <? if ($ul_open != (!$child->is_hidden() && !$child->driver("is_a_parent"))): ?>
        <? $ul_open = !$ul_open; ?>
        <?= $ul_open ? "<ul>" : "</ul>" ?>
      <? endif; ?>
      <?= $child->render() ?>
    <? endforeach; ?>
    <?= $ul_open ? "</ul>" : "" ?>
  </fieldset>
<?= $field->close() ?>
