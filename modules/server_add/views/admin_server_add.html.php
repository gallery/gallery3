<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-server-add-admin">
  <h2>
    <?= t("Add From Server Admininstration") ?>
  </h2>
  <div id="g-authorized-path">
    <h3><?= t("Authorized Paths") ?></h3>
    <ul<? if (!empty($paths)): ?> style="display: none;"<? endif ?>>
      <li class="g-module-status g-info"><?= t("No Authorized image source paths defined yet") ?></li>
    </ul>
    <ul id="g-path-list">
      <? foreach ($paths as $id => $path): ?>
      <li class="ui-icon-left">
        <a href="<?= url::site("admin/server_add/remove_path?path=" . urlencode($path) . "&amp;csrf=$csrf") ?>"
           id="icon_<?= $id?>"
           class="g-remove-dir ui-icon ui-icon-trash">
          X
        </a>
        <?= html::clean($path) ?>
      </li>
      <? endforeach ?>
    </ul>
  </div>
  <?= $form ?>
</div>
