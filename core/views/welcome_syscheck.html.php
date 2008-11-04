<? defined("SYSPATH") or die("No direct script access."); ?>
<? foreach ($errors as $error): ?>
<div class="block">
  <p class="error">
    <?= $error->message ?>
  </p>
  <? foreach ($error->instructions as $line): ?>
  <pre><?= $line ?></pre>
  <? endforeach ?>

  <? if (!empty($error->message2)): ?>
  <p class="error">
    <?= $error->message2 ?>
  </p>
  <? endif ?>
</div>
<? endforeach ?>

<? if (empty($errors)): ?>
<div class="block">
  <? if (empty($modules)): ?>
  <p class="success">
    Your system is ready, but Gallery is not yet installed.
  </p>
  <p>
    <?= html::anchor("welcome/install/core", "install gallery") ?>
  </p>
  <? else: ?>
  <p class="success">
    Your Gallery is ready with the following modules installed:
  </p>
  <table style="width: 400px">
    <tr>
      <th align="left">Name</th>
      <th align="left">Version</th>
      <th align="left">Action</th>
    </tr>
    <? foreach ($modules as $module): ?>
    <tr>
      <td><?= $module->name ?></td>
      <td><?= $module->version ?></td>
      <td>
        <?= html::anchor("welcome/install/{$module->name}", "install") ?>,
        <?= html::anchor("welcome/uninstall/{$module->name}", "uninstall") ?>
      </td>
    </tr>
    <? endforeach; ?>
  </table>
  <? endif; ?>
</div>
<? endif ?>
