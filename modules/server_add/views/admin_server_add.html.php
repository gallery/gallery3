<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block">
  <h1> <?= t("Add From Server Admininstration") ?> </h1>
  <div class="g-block-content">
    <h2><?= t("Authorized Paths") ?></h2>
    <ul<? if (!empty($paths)): ?> style="display: none;"<? endif ?>>
      <li class="g-module-status g-info"><?= t("No Authorized image source paths defined yet") ?></li>
    </ul>
    <ul>
      <? foreach ($paths as $id => $path): ?>
      <li class="ui-icon-left">
        <span class="ui-icon ui-icon-folder-open"></span>
        <?= html::clean($path) ?>
        <a href="<?= url::site("admin/server_add/remove_path?path=" . urlencode($path) . "&amp;csrf=$csrf") ?>"
           id="icon_<?= $id?>"
           class="g-remove-dir"><span class="ui-icon ui-icon-trash">X</span></a>
      </li>
      <? endforeach ?>
    </ul>
    <?= $form ?>
  </div>
</div>
