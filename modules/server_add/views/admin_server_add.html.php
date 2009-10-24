<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block">
  <h1> <?= t("Add From Server Admininstration") ?> </h1>
  <div class="g-block-content">
    <?= $form ?>
    <h2><?= t("Authorized Paths") ?></h2>
    <ul id="g-server-add-paths">
      <? if (empty($paths)): ?>
      <li class="g-module-status g-info"><?= t("No Authorized image source paths defined yet") ?></li>
      <? endif ?>
      <? foreach ($paths as $id => $path): ?>
      <li>
        <?= html::clean($path) ?>
        <a href="<?= url::site("admin/server_add/remove_path?path=" . urlencode($path) . "&amp;csrf=$csrf") ?>"
           id="icon_<?= $id ?>"
           class="g-remove-dir g-button"><span class="ui-icon ui-icon-trash"><?= t("delete") ?></span></a>
      </li>
      <? endforeach ?>
    </ul>
  </div>
</div>
