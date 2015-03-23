<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php echo form::open($action, array("method" => "post", "id" => "g-in-place-edit-form", "class" => "g-short-form")) ?>
  <?php echo access::csrf_form_field() ?>
  <ul>
    <li<?php if (!empty($errors["input"])): ?> class="g-error"<?php endif ?>>
      <?php echo form::input("input", $form["input"], " class=\"textbox\"") ?>
    </li>
    <li>
      <?php echo form::submit(array("class" => "submit ui-state-default"), t("Save")) ?>
    </li>
    <li><button class="g-cancel ui-state-default ui-corner-all"><?php echo t("Cancel") ?></button></li>
    <?php if (!empty($errors["input"])): ?>
    <li>
      <p id="g-in-place-edit-message" class="g-error"><?php echo $errors["input"] ?></p>
    </li>
    <?php endif ?>
  </ul>
</form>



