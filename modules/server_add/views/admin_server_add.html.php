<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gServerAddAdmin">
  <h2>
    <?= t("Add From Server Admininstration") ?>
  </h2>
  <div id="gAuthorizedPath">
    <span><?= t("Authorized Paths") ?></span>
    <ul id="gPathList">
      <? foreach ($paths as $id => $path): ?>
      <li class="ui-icon-left">
        <a href="<?= url::site("admin/server_add/remove_path?path=$path&csrf=" . access::csrf_token()) ?>"
           id="icon_<?= $id?>"
           class="gRemoveDir ui-icon ui-icon-trash">
          X
        </a>
        <?= $path ?>
      </li>
      <? endforeach ?>
    </ul>
    <div id="gNoAuthorizedPaths" <? if (!empty($paths)): ?>style="display:none"<? endif ?>>
      <span class="gWarning"><?= t("No Authorized image source paths defined") ?></span>
    </div>
  </div>
  <?= $form ?>
</div>
