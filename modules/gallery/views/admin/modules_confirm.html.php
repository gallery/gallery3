<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="ui-helper-clearfix">
  <p>
    <?= t("The following issue(s) have been identified:") ?>
  </p>
  <div id="g-admin-modules-messages" class="g-block-content">
  <ul>
    <? foreach (array("error" => "g-error", "warn" => "g-warning") as $type => $css_class): ?>
    <? foreach ($messages[$type] as $message): ?>
    <li class="<?= $css_class ?>" style="padding-bottom: 0"><?= $message ?></li>
    <? endforeach ?>
    <? endforeach ?>
  </ul>
    <?= $form->open() ?>
      <? foreach (array_merge($form->as_array(), $form->modules->as_array()) as $field): ?>
      <?= $field->is_hidden() ? $field->open() . $field->close() : ""?>
      <? endforeach; ?>
    <?= $form->close() ?>
  </div>
</div>
