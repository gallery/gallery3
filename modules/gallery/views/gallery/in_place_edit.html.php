<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= Form::open($action, array("method" => "post", "id" => "g-in-place-edit-form", "class" => "g-short-form")) ?>
  <?= Access::csrf_form_field() ?>
  <ul>
    <li<? if (!empty($errors["input"])): ?> class="g-error"<? endif ?>>
      <?= Form::input("input", $form["input"], " class=\"textbox\"") ?>
    </li>
    <li>
      <?= Form::submit(array("class" => "submit ui-state-default"), t("Save")) ?>
    </li>
    <li><button class="g-cancel ui-state-default ui-corner-all"><?= t("Cancel") ?></button></li>
    <? if (!empty($errors["input"])): ?>
    <li>
      <p id="g-in-place-edit-message" class="g-error"><?= $errors["input"] ?></p>
    </li>
    <? endif ?>
  </ul>
</form>



