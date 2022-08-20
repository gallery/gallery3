<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= form::open($action, array("method" => "post", "id" => "g-in-place-edit-form", "class" => "g-short-form")) ?>
  <?= access::csrf_form_field() ?>
  <ul>
    <li<?php if (!empty($errors["input"])): ?> class="g-error"<?php endif ?>>
      <?= form::input("input", $form["input"], " class=\"textbox\"") ?>
    </li>
    <li>
      <?= form::submit(array("class" => "submit ui-state-default"), t("Save")) ?>
    </li>
    <li><button class="g-cancel ui-state-default ui-corner-all"><?= t("Cancel") ?></button></li>
    <?php if (!empty($errors["input"])): ?>
    <li>
      <p id="g-in-place-edit-message" class="g-error"><?= $errors["input"] ?></p>
    </li>
    <?php endif ?>
  </ul>
</form>



