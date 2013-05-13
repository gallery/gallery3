<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // Render the form.  The code here is very similar to that of group_template. ?>
<?= $field->open() ?>
  <? if ($title): ?>
    <h1>
      <?= $title ?>
    </h1>
  <? endif; ?>
  <? if ($field->html()): ?>
    <? foreach ((is_array($field->html()) ? $field->html() : array($field->html())) as $html): ?>
      <p>
        <?= $html ?>
      </p>
    <? endforeach; ?>
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
<?= $field->close() ?>
