<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= form::open($action, array("method" => "post", "id" => "g-in-place-edit-form", "class" => "g-short-form"), $hidden) ?>
  <ul>
    <li <? if (!empty($errors["input"])): ?> class="g-error"<? endif ?>>
      <?= form::input("input", $form["input"], " class='textbox'") ?>
    </li>
    <li>
      <?= form::submit(array("class" => "submit ui-state-default"), t("Save")) ?>
    </li>
    <li><a href="#" class="g-cancel"><?= t("Cancel") ?></a></li>
  </ul>
<?= form::close() ?>
<? if (!empty($errors["input"])): ?>
<div id="g-in-place-edit-message" class="g-error"><?= $errors["input"] ?></div>
<? endif ?>

