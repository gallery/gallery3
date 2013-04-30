<? list ($groups, $non_groups, $hidden) = $field->sort_children(); ?>
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
    <? // Render the hidden objects first, which have no <li> tags. ?>
    <? foreach ($hidden as $child): ?>
    <?= $child->render() ?>
    <? endforeach; ?>
    <? // Render the viewable non-group objects next, which need <ul> tags. ?>
    <? if ($non_groups): ?>
    <ul>
      <? foreach ($non_groups as $child): ?>
      <?= $child->render() ?>
      <? endforeach; ?>
    </ul>
    <? endif; ?>
    <? // Render the groups last, which will set their own <fieldset> tags. ?>
    <? foreach ($groups as $child): ?>
    <?= $child->render() ?>
    <? endforeach; ?>
  </fieldset>
<?= $field->close() ?>
