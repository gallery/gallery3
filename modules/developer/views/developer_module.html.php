<?php defined("SYSPATH") or die("No direct script access.") ?>
<script>
$("#gModuleCreateForm").ready(function() {
  ajaxify_developer_form("#gModuleCreateForm form",  module_success);
});
 
</script>
<div id="gModuleCreateForm">
  <?= form::open($action, array("method" => "post"), $hidden) ?>
    <ul>
      <li <? if (!empty($errors["name"])): ?> class="gError"<? endif ?>>
         <?= form::label("name", t("Name")) ?>
         <?= form::input("name", $form["name"]) ?>
         <? if (!empty($errors["name"]) && $errors["name"] == "required"): ?>
           <p class="gError"><?= t("Module name is required") ?></p>
         <? endif ?>
         <? if (!empty($errors["name"]) && $errors["name"] == "module_exists"): ?>
           <p class="gError"><?= t("Module is already implemented") ?></p>
         <? endif ?>
      </li>
      <li <? if (!empty($errors["description"])): ?> class="gError"<? endif ?>>
         <?= form::label("description", t("Description")) ?>
         <?= form::input("description", $form["description"]) ?>
         <? if (!empty($errors["description"]) && $errors["description"] == "required"): ?>
           <p class="gError"><?= t("Module description is required")?></p>
         <? endif ?>
      </li>
      <li>
        <ul>
           <li>
              <?= form::label("theme[]", t("Theme Callbacks")) ?>
              <?= form::dropdown(array("name" => "theme[]", "multiple" => true, "size" => 6), $theme, $form["theme[]"]) ?>
           </li>
           <li>
              <?= form::label("block[]", t("Block Callbacks")) ?>
              <?= form::dropdown(array("name" => "block[]", "multiple" => true, "size" => 6), $block, $form["block[]"]) ?>
           </li>
           <li>
              <?= form::label("menu[]", t("Menu Callback")) ?>
              <?= form::dropdown(array("name" => "menu[]", "multiple" => true, "size" => 6), $menu, $form["block[]"]) ?>
           </li>
           <li>
              <?= form::label("event[]", t("Gallery Event Handlers")) ?>
              <?= form::dropdown(array("name" => "event[]", "multiple" => true, "size" => 6), $event, $form["event[]"]) ?>
           </li>
        </ul>
      </li>
      <li>
         <?= form::submit(array("id" => "gGenerateModule", "name" => "generate", "class" => "submit"), t("Generate")) ?>
      </li>
    </ul>
  <?= form::close() ?>
</div>
